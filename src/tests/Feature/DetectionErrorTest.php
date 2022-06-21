<?php

namespace Tests\Feature;

use App\DeepstackClient;
use App\DetectionEvent;
use App\DetectionProfile;
use App\DeepstackCall;
use App\ImageFile;
use App\Exceptions\DeepstackException;
use App\Jobs\ProcessAutomationJob;
use App\Jobs\ProcessDetectionEventJob;
use App\Jobs\ProcessImageOptimizationJob;
use App\Mocks\FakeDeepstackClient;
use App\WebRequestConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Log;


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

    protected function handleDetectionJob(DetectionEvent $event, $compressImage = true, $imageQuality = 75, $privacy_mode = false)
    {
        $compressionSettings = [
            'compress_images' => $compressImage,
            'image_quality' => $imageQuality,
        ];

        $job = new ProcessDetectionEventJob($event, $compressionSettings, $privacy_mode);
        $job->handle(new FakeDeepstackClient('{
              "success": false,
              "error": "failed to process request before timeout",
              "duration": 0
            }'));
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
            'is_processed' => false
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);

        $this->expectException(DeepstackException::class);
        try {
          $this->handleDetectionJob($event);
        }
        finally {
          Queue::assertNotPushed(ProcessImageOptimizationJob::class);
          $event->refresh()->load(['aiPredictions', 'detectionProfiles', 'deepstackCall']);
          $this->assertFalse($event->is_processed);
          $this->assertNotNull($event->deepstackCall);
          $this->assertTrue($event->deepstackCall->is_error);
          $this->assertFalse($event->deepstackCall->success);
          $this->assertCount(0, $event->aiPredictions);
          $this->assertCount(0, $event->detectionProfiles);
        }
    }
}
