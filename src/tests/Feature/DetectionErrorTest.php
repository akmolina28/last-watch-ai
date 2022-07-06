<?php

namespace Tests\Feature;

use App\DeepstackClient;
use App\DetectionEvent;
use App\DetectionProfile;
use App\Exceptions\DeepstackException;
use App\ImageFile;
use App\Jobs\ProcessDetectionEventJob;
use App\Jobs\ProcessImageOptimizationJob;
use App\Mocks\FakeDeepstackClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DetectionErrorTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    // use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        Storage::fake('public');

        app()->bind(DeepstackClient::class, function () {
            return new FakeDeepstackClient();
        });
    }

    protected function createImageFile($fileName = 'testimage.jpg'): ImageFile
    {
        Storage::fake('public');
        $imageFile = UploadedFile::fake()->image($fileName, 640, 480)->size(128);
        $path = $imageFile->storeAs('events', $fileName);

        return ImageFile::create([
            'path' => $path,
            'file_name' => $fileName,
            'width' => 640,
            'height' => 480,
        ]);
    }

    protected function handleDetectionJob(DetectionEvent $event, $json_response = null)
    {
        $compressionSettings = [
            'compress_images' => true,
            'image_quality' => 75,
        ];

        $job = new ProcessDetectionEventJob($event, $compressionSettings, false);
        $job->handle(new FakeDeepstackClient($json_response));
    }

    /**
     * @test
     */
    public function detection_job_handles_timeout_exceptions()
    {
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => false,
        ]);

        $imageFile = $this->createImageFile();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
            'is_processed' => false,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);

        $this->expectException(DeepstackException::class);
        try {
            $this->handleDetectionJob($event, '{
            "success": false,
            "error": "failed to process request before timeout",
            "duration": 0
          }');
        } finally {
            Queue::assertNotPushed(ProcessImageOptimizationJob::class);
            $event->refresh()->load(['aiPredictions', 'detectionProfiles', 'deepstackCalls']);
            $this->assertFalse($event->is_processed);
            $this->assertCount(1, $event->deepstackCalls);
            $this->assertTrue($event->deepstackCalls()->first()->is_error);
            $this->assertFalse($event->deepstackCalls()->first()->success);
            $this->assertCount(0, $event->aiPredictions);
            $this->assertCount(0, $event->detectionProfiles);
        }
    }

    /**
     * @test
     */
    public function detection_job_can_retry_after_timeout_exception()
    {
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => false,
        ]);

        $imageFile = $this->createImageFile();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
            'is_processed' => false,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);

        $this->expectException(DeepstackException::class);
        try {
            $this->handleDetectionJob($event, '{
            "success": false,
            "error": "failed to process request before timeout",
            "duration": 0
          }');
        } finally {
            Queue::assertNotPushed(ProcessImageOptimizationJob::class);
            $event->refresh()->load(['aiPredictions', 'detectionProfiles', 'deepstackCalls']);
            $this->assertFalse($event->is_processed);
            $this->assertCount(1, $event->deepstackCalls);
            $this->assertTrue($event->deepstackCalls()->first()->is_error);

            // retry
            $this->handleDetectionJob($event);
            $event->refresh()->load(['aiPredictions', 'detectionProfiles', 'deepstackCalls']);
            $this->assertCount(3, $event->aiPredictions);
            $this->assertCount(3, $event->detectionProfiles);
        }
    }
}
