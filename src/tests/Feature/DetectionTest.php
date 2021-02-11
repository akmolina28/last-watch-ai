<?php

namespace Tests\Feature;

use App\AutomationConfig;
use App\DeepstackClient;
use App\DetectionEvent;
use App\DetectionProfile;
use App\Jobs\ProcessAutomationJob;
use App\Jobs\ProcessDetectionEventJob;
use App\Mocks\FakeDeepstackClient;
use App\WebRequestConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DetectionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        Storage::fake('public');

        app()->bind(DeepstackClient::class, function () {
            return new FakeDeepstackClient();
        });
    }

    protected function setUpTestImage()
    {
        $imageFile = UploadedFile::fake()->image('testimage.jpg', 640, 480)->size(128);
        $imageFile->storeAs('events', 'testimage.jpg');
    }

    protected function handleDetectionJob(DetectionEvent $event)
    {
        $job = new ProcessDetectionEventJob($event);
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

        $event = factory(DetectionEvent::class)->create([
            'image_file_name' => 'events/testimage.jpg',
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);

        $this->setUpTestImage();

        Storage::assertExists('events/testimage.jpg');

        $this->handleDetectionJob($event);

        $event->refresh()->load(['aiPredictions', 'detectionProfiles', 'deepstackCall']);

        $this->assertNotNull($event->deepstackCall);
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);
    }

    /**
     * @test
     */
    public function detection__job_creates_relevant_relationship_for_active_matches()
    {
        factory(DetectionProfile::class, 5)->create();

        // active match
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['person', 'dog'],
            'use_mask' => false,
        ]);
        $event = factory(DetectionEvent::class)->create([
            'image_file_name' => 'events/testimage.jpg',
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

        $event->refresh()->load(['aiPredictions.detectionProfiles', 'deepstackCall']);

        $this->assertNotNull($event->deepstackCall);
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

        // process an event
        $event = factory(DetectionEvent::class)->create([
            'image_file_name' => 'events/testimage.jpg',
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);
        $this->handleDetectionJob($event);

        // process another event with the same predictions
        $event = factory(DetectionEvent::class)->create([
            'image_file_name' => 'events/testimage.jpg',
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);
        $this->handleDetectionJob($event);

        $event = DetectionEvent::find($event->id);
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);

        foreach ($event->detectionProfiles as $profile) {
            $this->assertEquals(1, $profile->ai_prediction_detection_profile->is_smart_filtered);
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
            'name' => 'test-mask3',
        ]);

        // process an event
        $event = factory(DetectionEvent::class)->create([
            'image_file_name' => 'events/testimage.jpg',
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);
        $this->handleDetectionJob($event);

        $event = DetectionEvent::find($event->id);

        // 3 total predictions
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);

        // only 2 unmasked predictions
        $this->assertCount(2, $event->detectionProfiles()
            ->where('ai_prediction_detection_profile.is_masked', '=', false)->get());
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

        // process an event
        $event = factory(DetectionEvent::class)->create([
            'image_file_name' => 'events/testimage.jpg',
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

        AutomationConfig::create([
            'detection_profile_id' => $profile->id,
            'automation_config_id' => $webRequestAutomation->id,
            'automation_config_type' => 'web_request_configs'
        ]);

        $event = factory(DetectionEvent::class)->create([
            'image_file_name' => 'events/testimage.jpg',
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);

        $this->setUpTestImage();

        Storage::assertExists('events/testimage.jpg');

        $this->handleDetectionJob($event);

        $event->refresh()->load(['aiPredictions', 'detectionProfiles', 'deepstackCall']);

        $this->assertNotNull($event->deepstackCall);
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

        AutomationConfig::create([
            'detection_profile_id' => $profile->id,
            'automation_config_id' => $webRequestAutomation->id,
            'automation_config_type' => 'web_request_configs',
            'is_high_priority' => true
        ]);

        $event = factory(DetectionEvent::class)->create([
            'image_file_name' => 'events/testimage.jpg',
        ]);
        $event->patternMatchedProfiles()->attach($profile->id);

        $this->setUpTestImage();

        Storage::assertExists('events/testimage.jpg');

        $this->handleDetectionJob($event);

        $event->refresh()->load(['aiPredictions', 'detectionProfiles', 'deepstackCall']);

        $this->assertNotNull($event->deepstackCall);
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);

        Queue::assertPushedOn('high', ProcessAutomationJob::class);
    }
}
