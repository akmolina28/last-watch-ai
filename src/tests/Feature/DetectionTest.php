<?php

namespace Tests\Feature;

use App\DeepstackClient;
use App\DetectionEvent;
use App\DetectionProfile;
use App\Mocks\FakeDeepstackClient;
use App\Jobs\ProcessDetectionEventJob;
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

        Storage::fake('public');

        Queue::fake();

        $this->setUpTestImage();

        app()->bind(DeepstackClient::class, function() { // not a service provider but the target of service provider
            return new FakeDeepstackClient();
        });
    }

    protected function setUpTestImage() {
        $imageFile = UploadedFile::fake()->image('testimage.jpg', 640, 480)->size(128);
        $file = $imageFile->storeAs('events','testimage.jpg');
    }

    protected function handleDetectionJob(DetectionEvent $event) {
        $job = new ProcessDetectionEventJob($event);
        $job->handle(new FakeDeepstackClient());
    }

    /**
     * @test
     */
    public function detection_job_creates_relevant_relationship()
    {
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['car', 'person'],
            'use_mask' => false
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
            'object_classes' => ['car', 'person'],
            'use_mask' => false
        ]);
        $event = factory(DetectionEvent::class)->create();
        $event->patternMatchedProfiles()->attach($profile->id);

        // inactive match
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['car', 'person'],
            'use_mask' => false
        ]);
        $event->patternMatchedProfiles()->attach($profile->id, [
            'is_profile_active' => false
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
            'object_classes' => ['car', 'person'],
            'use_mask' => false,
            'use_smart_filter' => true
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
}
