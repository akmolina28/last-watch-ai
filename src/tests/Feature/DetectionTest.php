<?php

namespace Tests\Feature;

use App\DeepstackClient;
use App\DetectionEvent;
use App\DetectionProfile;
use App\Jobs\ProcessDetectionEventJob;
use App\Mocks\FakeDeepstackClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DetectionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        app()->bind(DeepstackClient::class, function () {
            return new FakeDeepstackClient();
        });
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

        $event = factory(DetectionEvent::class)->create();
        $event->patternMatchedProfiles()->attach($profile->id);

        $this->handleDetectionJob($event);

        $event->refresh()->load(['aiPredictions']);
        $event->refresh()->load(['detectionProfiles']);

        $this->assertNotNull($event->deepstack_response);
        $this->assertCount(3, $event->aiPredictions);
        $this->assertCount(3, $event->detectionProfiles);
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
        $event = factory(DetectionEvent::class)->create();
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

        $event->refresh()->load(['aiPredictions.detectionProfiles']);

        $this->assertNotNull($event->deepstack_response);
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
        $event = factory(DetectionEvent::class)->create();
        $event->patternMatchedProfiles()->attach($profile->id);
        $this->handleDetectionJob($event);

        // process another event with the same predictions
        $event = factory(DetectionEvent::class)->create();
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
        $event = factory(DetectionEvent::class)->create();
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
        $event = factory(DetectionEvent::class)->create();
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
}
