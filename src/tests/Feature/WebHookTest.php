<?php

namespace Tests\Feature;

use App\DetectionEvent;
use App\DetectionProfile;
use App\Jobs\ProcessDetectionEventJob;
use App\Jobs\ProcessWebhookJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\WebhookClient\Models\WebhookCall;
use Tests\TestCase;

class WebHookTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Queue::fake();

        $this->setUpTestImage();
    }

    protected function setUpTestImage() {
        $imageFile = UploadedFile::fake()->image('testimage.jpg', 640, 480)->size(128);
        $file = $imageFile->storeAs('events','testimage.jpg');
    }

    protected function handleWebhookJob() {
        $webhookCall = new WebhookCall();
        $webhookCall->payload = [
            'file' => 'testimage.jpg'
        ];
        $job = new ProcessWebhookJob($webhookCall);
        $job->handle();
    }

    /**
     * @test
     */
    public function webhook_creates_a_queued_job()
    {
        // hit the webhook
        $this->post('/webhook-receiving-url', [
            'file' => 'testimage.jpg'
        ])->assertStatus(200);

        // check for webhook job on queue and process it
        Queue::assertPushed(ProcessWebhookJob::class, function ($job) {
            return $job->webhookCall->payload['file'] === 'testimage.jpg';
        });
    }

    /**
     * @test
     */
    public function webhook_job_can_create_non_matched_detection_event() {
        $this->handleWebhookJob();

        // see that detection event was generated
        $event = DetectionEvent::first();
        $this->assertEquals('events/testimage.jpg', $event->image_file_name);
        $this->assertEquals('640x480', $event->image_dimensions);
        $this->assertCount(0, $event->patternMatchedProfiles);
    }

    /**
     * @test
     */
    public function webhook_can_create_a_detection_event_with_one_match() {
        // create some dummy profiles
        factory(DetectionProfile::class, 5)->create();

        // create a profile to match the event
        $profile = factory(DetectionProfile::class)->make();
        $profile->file_pattern = 'testimage';
        $profile->use_regex = false;
        $profile->save();

        // hit the webhook
        $this->handleWebhookJob();

        // see that detection event was generated
        $this->assertDatabaseCount('detection_events', 1);
        $event = DetectionEvent::with(['patternMatchedProfiles'])->first();
        $this->assertEquals('events/testimage.jpg', $event->image_file_name);
        $this->assertEquals('640x480', $event->image_dimensions);
        $this->assertCount(1, $event->patternMatchedProfiles);

        Queue::assertPushed(ProcessDetectionEventJob::class, function ($job) {
            return $job->event->image_file_name === 'events/testimage.jpg';
        });
    }

    /**
     * @test
     */
    public function webhook_can_create_a_detection_event_with_many_matches() {
        // create some dummy profiles
        factory(DetectionProfile::class, 5)->create();

        // create some profile to match the event
        factory(DetectionProfile::class, 3)->create([
            'file_pattern' => 'testimage',
            'use_regex' => false
        ]);

        // hit the webhook
        $this->handleWebhookJob();

        // see that detection event was generated
        $this->assertDatabaseCount('detection_events', 1);
        $event = DetectionEvent::first();
        $this->assertEquals('events/testimage.jpg', $event->image_file_name);
        $this->assertEquals('640x480', $event->image_dimensions);
        $this->assertCount(3, $event->patternMatchedProfiles);

        Queue::assertPushed(ProcessDetectionEventJob::class, function ($job) {
            return $job->event->image_file_name === 'events/testimage.jpg';
        });
    }

    /**
     * @test
     */
    public function webhook_can_an_ignore_inactive_profile() {
        // create a profile to match the event
        $profile = factory(DetectionProfile::class)->make();
        $profile->file_pattern = 'testimage';
        $profile->use_regex = false;

        // inactive
        $profile->is_active = false;

        $profile->save();

        // hit the webhook
        $this->handleWebhookJob();

        // see that detection event was generated
        $this->assertDatabaseCount('detection_events', 1);
        $event = DetectionEvent::first();
        $this->assertEquals('events/testimage.jpg', $event->image_file_name);
        $this->assertEquals('640x480', $event->image_dimensions);
        $this->assertCount(0, $event->patternMatchedProfiles);

        Queue::assertNothingPushed();
    }
}
