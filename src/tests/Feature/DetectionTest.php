<?php

namespace Tests\Feature;

use App\DeepstackClient;
use App\DetectionEvent;
use App\DetectionProfile;
use App\ImageFile;
use App\Jobs\ProcessAutomationJob;
use App\Jobs\ProcessDetectionEventJob;
use App\Jobs\ProcessImageOptimizationJob;
use App\Mocks\FakeDeepstackClient;
use App\WebRequestConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DetectionTest extends TestCase
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
        $job->handle(new FakeDeepstackClient());
    }

    /**
     * @test
     */
    public function detection_job_creates_relevant_relationship()
    {
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => false,
        ]);

        $imageFile = $this->createImageFile();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);

        $this->handleDetectionJob($event);

        $event->refresh()->load(['aiPredictions', 'detectionProfiles', 'deepstackCalls']);

        $this->assertCount(1, $event->deepstackCalls);
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);

        $this->assertCount(3, $event->detectionProfiles->where('ai_prediction_detection_profile.is_relevant', '=', true));
    }

    /**
     * @test
     */
    public function detection_job_creates_relevant_relationship_for_active_matches()
    {
        factory(DetectionProfile::class, 5)->create();

        // active match
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => false,
        ]);

        $imageFile = $this->createImageFile();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);

        // inactive match
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => false,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id, [
            'is_profile_active' => false,
        ]);

        $this->handleDetectionJob($event);

        $event->refresh()->load(['aiPredictions.detectionProfiles', 'deepstackCalls']);

        $this->assertCount(1, $event->deepstackCalls);
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);
    }

    /**
     * @test
     */
    public function detection_job_can_smart_filter_predictions()
    {
        factory(DetectionProfile::class, 5)->create();

        // active match
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => false,
            'use_smart_filter' => true,
            'smart_filter_precision' => '0.90',
        ]);

        $imageFile = $this->createImageFile();

        // process an event
        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);
        $this->handleDetectionJob($event);

        // process another event with the same predictions
        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);
        $this->handleDetectionJob($event);

        $event = DetectionEvent::find($event->id);
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);

        foreach ($event->detectionProfiles as $profile) {
            $this->assertEquals(1, $profile->ai_prediction_detection_profile->is_smart_filtered);
            $this->assertEquals(0, $profile->ai_prediction_detection_profile->is_relevant);
        }
    }

    /**
     * @test
     */
    public function detection_job_can_confidence_filter_predictions()
    {
        factory(DetectionProfile::class, 5)->create();

        // active match
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => false,
            'use_smart_filter' => false,
            'min_confidence' => '1.00',
        ]);

        $imageFile = $this->createImageFile();

        // process an event
        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);
        $this->handleDetectionJob($event);

        // process another event with the same predictions
        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);
        $this->handleDetectionJob($event);

        $event = DetectionEvent::find($event->id);
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);

        foreach ($event->detectionProfiles as $profile) {
            $this->assertEquals(1, $profile->ai_prediction_detection_profile->is_confidence_filtered);
            $this->assertEquals(0, $profile->ai_prediction_detection_profile->is_relevant);
        }
    }

    /**
     * @test
     */
    public function detection_job_can_mask_predictions()
    {
        factory(DetectionProfile::class, 5)->create();

        // active match
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => true,
            'name' => 'test-mask3', // pulls mask file from storage/app/public_testing/masks
        ]);

        $imageFile = $this->createImageFile();

        // process an event
        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);
        $this->handleDetectionJob($event);

        $event = DetectionEvent::find($event->id);

        // 3 total predictions
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);

        // only 2 unmasked predictions
        $this->assertCount(2, $event->detectionProfiles()
            ->where('ai_prediction_detection_profile.is_masked', '=', false)
            ->where('ai_prediction_detection_profile.is_relevant', '=', true)
            ->get()
        );

        // only 1 masked prediction
        $this->assertCount(1, $event->detectionProfiles()
            ->where('ai_prediction_detection_profile.is_masked', '=', true)
            ->where('ai_prediction_detection_profile.is_relevant', '=', false)
            ->get()
        );
    }

    /**
     * @test
     */
    public function detection_job_can_filter_by_min_object_size()
    {
        factory(DetectionProfile::class, 5)->create();

        // // active match
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => false,
            'min_object_size' => 30000, // should filter out the smallest prediction, the dog
        ]);

        $imageFile = $this->createImageFile();

        // process an event
        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);
        $this->handleDetectionJob($event);

        $event = DetectionEvent::find($event->id);

        // 3 total predictions
        $this->assertCount(3, $event->aiPredictions);

        // only 2 unfiltered predictions
        $this->assertCount(2, $event->detectionProfiles()
            ->where('ai_prediction_detection_profile.is_size_filtered', '=', false)
            ->where('ai_prediction_detection_profile.is_relevant', '=', true)
            ->get()
        );

        // 1 filtered prediction (dog)
        $this->assertCount(1, $event->detectionProfiles()
            ->where('ai_prediction_detection_profile.is_size_filtered', '=', true)
            ->where('ai_prediction_detection_profile.is_relevant', '=', false)
            ->get()
        );

        $this->assertEquals('dog', $profile->aiPredictions()->where('is_size_filtered', '=', true)->first()->object_class);
    }

    /**
     * @test
     */
    public function detection_job_can_relate_profile_by_object_class()
    {
        // active match
        $personProfile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person'],
            'use_mask' => false,
        ]);

        // active match
        $dogProfile = factory(DetectionProfile::class)->create([
            'object_classes' => ['dog'],
            'use_mask' => false,
        ]);

        // active match
        $carProfile = factory(DetectionProfile::class)->create([
            'object_classes' => ['car'],
            'use_mask' => false,
        ]);

        $imageFile = $this->createImageFile();

        // process an event
        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);
        $event->patternMatchedProfiles()->attach([
            $personProfile->id,
            $dogProfile->id,
            $carProfile->id,
        ]);
        $this->handleDetectionJob($event);

        $event = DetectionEvent::find($event->id);

        // 3 total predictions
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);

        $this->assertCount(2, $personProfile->aiPredictions);
        $this->assertCount(1, $dogProfile->aiPredictions);
        $this->assertCount(0, $carProfile->aiPredictions);
    }

    /**
     * @test
     */
    public function detection_job_queues_an_automation()
    {
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => false,
        ]);

        $webRequestAutomation = factory(WebRequestConfig::class)->create();

        $profile->subscribeAutomation(WebRequestConfig::class, $webRequestAutomation->id);

        $imageFile = $this->createImageFile();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);

        $this->handleDetectionJob($event);

        $event->refresh()->load(['aiPredictions', 'detectionProfiles', 'deepstackCalls']);

        $this->assertCount(1, $event->deepstackCalls);
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);

        Queue::assertPushedOn('low', ProcessAutomationJob::class);
    }

    /**
     * @test
     */
    public function detection_job_queues_a_high_priority_automation()
    {
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => false,
        ]);

        $webRequestAutomation = factory(WebRequestConfig::class)->create();

        $profile->subscribeAutomation(WebRequestConfig::class, $webRequestAutomation->id, true);

        $imageFile = $this->createImageFile();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);

        $this->handleDetectionJob($event);

        $event->refresh()->load(['aiPredictions', 'detectionProfiles', 'deepstackCalls']);

        $this->assertCount(1, $event->deepstackCalls);
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);

        Queue::assertPushedOn('high', ProcessAutomationJob::class);
    }

    /**
     * @test
     */
    public function detection_job_creates_image_compression_job()
    {
        $imageFile = $this->createImageFile();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);

        $this->handleDetectionJob($event, true);
        Queue::assertPushedOn('low', ProcessImageOptimizationJob::class, function ($job) {
            return $job->privacy_mode === false;
        });
    }

    /**
     * @test
     */
    public function detection_job_creates_private_image_compression_job()
    {
        $imageFile = $this->createImageFile();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);

        $this->handleDetectionJob($event, true, 75, true);

        Queue::assertPushedOn('low', ProcessImageOptimizationJob::class, function ($job) {
            return $job->privacy_mode === true;
        });
    }
}
