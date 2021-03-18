<?php

namespace Tests\Feature;

use App\DetectionEvent;
use App\DetectionProfile;
use App\Jobs\ProcessDetectionEventJob;
use App\Jobs\ProcessEventUploadJob;
use App\Jobs\ProcessWebhookJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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

    protected function setUpTestImage()
    {
        $imageFile = UploadedFile::fake()->image('testimage.jpg', 640, 480)->size(128);
        $imageFile->storeAs('events', 'testimage.jpg');
    }

    protected function handleWebhookJob(Carbon $occurred_at = null)
    {
        $webhookCall = new WebhookCall();
        $webhookCall->payload = [
            'file' => 'testimage.jpg',
        ];
        $job = new ProcessWebhookJob($webhookCall);
        $job->handle($occurred_at);
    }

    protected function triggerWebhook($fileName = 'testimage.jpg', $occurredAt = null)
    {
        if (! $occurredAt) {
            $occurredAt = Carbon::now();
        }

        $imageFile = UploadedFile::fake()->image($fileName, 640, 480)->size(128);
        $path = $imageFile->storeAs('events', $fileName);

        $uploadJob = new ProcessEventUploadJob($path, $fileName, $occurredAt);

        $uploadJob->handle();

        return $path;
    }

    /**
     * @test
     */
    public function webhook_creates_a_queued_event_job()
    {
        $imageFile = UploadedFile::fake()->image('testimage.jpg', 640, 480)->size(128);

        // hit the webhook
        $this->json('POST', '/api/events', [
            'image_file' => $imageFile,
        ], [
            'enctype' => 'multipart/form-data',
        ])->assertStatus(201);

        // check for webhook job on queue and process it
        Queue::assertPushedOn('medium', ProcessEventUploadJob::class, function ($job) {
            return $job->fileName === 'testimage.jpg';
        });
    }

    /**
     * @test
     */
    public function event_upload_job_creates_a_detection_job()
    {
        // create a matched profile
        factory(DetectionProfile::class)->create([
            'file_pattern' => 'testimage',
        ]);

        $this->triggerWebhook();

        Queue::assertPushedOn('medium', ProcessDetectionEventJob::class, function ($job) {
            return $job->event->imageFile->file_name === 'testimage.jpg';
        });
    }

    /**
     * @test
     */
    public function webhook_job_can_create_non_matched_detection_event()
    {
        $this->triggerWebhook();

        // see that detection event was generated
        $event = DetectionEvent::first();
        $this->assertEquals('testimage.jpg', $event->imageFile->file_name);
        $this->assertEquals(640, $event->imageFile->width);
        $this->assertEquals(480, $event->imageFile->height);
        $this->assertCount(0, $event->patternMatchedProfiles);
    }

    /**
     * @test
     */
    public function webhook_can_create_a_detection_event_with_one_match()
    {
        // create some dummy profiles
        factory(DetectionProfile::class, 5)->create([
            'file_pattern' => 'fakepattern123',
        ]);

        // create a profile to match the event
        factory(DetectionProfile::class)->create([
            'file_pattern' => 'testimage',
            'use_regex' => false,
        ]);

        // hit the webhook
        $this->triggerWebhook();

        // see that detection event was generated
        $this->assertDatabaseCount('detection_events', 1);
        $event = DetectionEvent::with(['patternMatchedProfiles'])->first();
        $this->assertEquals('testimage.jpg', $event->imageFile->file_name);
        $this->assertEquals(640, $event->imageFile->width);
        $this->assertEquals(480, $event->imageFile->height);
        $this->assertCount(1, $event->patternMatchedProfiles);
        $this->assertEquals(1, $event->patternMatchedProfiles()->first()->pivot->is_profile_active);

        Queue::assertPushedOn('medium', ProcessDetectionEventJob::class, function ($job) {
            return $job->event->imageFile->file_name === 'testimage.jpg';
        });
    }

    /**
     * @test
     */
    public function webhook_can_create_a_detection_event_with_many_matches()
    {
        // create some dummy profiles
        factory(DetectionProfile::class, 5)->create([
            'file_pattern' => 'fakepattern123',
        ]);

        // create some profile to match the event
        factory(DetectionProfile::class, 3)->create([
            'file_pattern' => 'testimage',
            'use_regex' => false,
        ]);

        // hit the webhook
        $this->triggerWebhook();

        // see that detection event was generated
        $this->assertDatabaseCount('detection_events', 1);
        $event = DetectionEvent::first();
        $this->assertEquals('testimage.jpg', $event->imageFile->file_name);
        $this->assertEquals(640, $event->imageFile->width);
        $this->assertEquals(480, $event->imageFile->height);
        $this->assertCount(3, $event->patternMatchedProfiles);

        Queue::assertPushedOn('medium', ProcessDetectionEventJob::class, function ($job) {
            return $job->event->imageFile->file_name === 'testimage.jpg';
        });
    }

    /**
     * @test
     */
    public function webhook_job_creates_inactive_match_for_inactive_profile()
    {
        // create a profile to match the event
        $profile = factory(DetectionProfile::class)->make();
        $profile->file_pattern = 'testimage';
        $profile->use_regex = false;

        // inactive
        $profile->is_enabled = false;

        $profile->save();

        // hit the webhook
        $this->triggerWebhook();

        // see that detection event was generated
        $this->assertDatabaseCount('detection_events', 1);

        $event = DetectionEvent::first();
        $this->assertCount(1, $event->patternMatchedProfiles);
        $this->assertEquals(0, $event->patternMatchedProfiles()->first()->pivot->is_profile_active);
    }

    /**
     * @test
     */
    public function webhook_job_creates_active_match_for_scheduled_profile()
    {
        // create a profile to match the event
        $profile = factory(DetectionProfile::class)->make();
        $profile->file_pattern = 'testimage';
        $profile->use_regex = false;

        // schedule
        $profile->is_scheduled = true;
        $profile->start_time = '01:00';
        $profile->end_time = '02:00';

        $profile->save();

        // hit the webhook
        $occurredAt = Carbon::create(2020, 1, 1, 1, 25, 33);
        $this->triggerWebhook('testimage.jpg', $occurredAt);

        // see that detection event was generated
        $this->assertDatabaseCount('detection_events', 1);

        $event = DetectionEvent::first();
        $this->assertCount(1, $event->patternMatchedProfiles);
        $this->assertEquals(1, $event->patternMatchedProfiles()->first()->pivot->is_profile_active);
    }

    /**
     * @test
     */
    public function webhook_job_creates_inactive_match_for_scheduled_profile()
    {
        // create a profile to match the event
        $profile = factory(DetectionProfile::class)->make();
        $profile->file_pattern = 'testimage';
        $profile->use_regex = false;

        // schedule
        $profile->is_scheduled = true;
        $profile->start_time = '01:00';
        $profile->end_time = '02:00';

        $profile->save();

        // hit the webhook
        $occurredAt = Carbon::create(2020, 1, 1, 3, 25, 33);
        $this->triggerWebhook('testimage.jpg', $occurredAt);

        // see that detection event was generated
        $this->assertDatabaseCount('detection_events', 1);

        $event = DetectionEvent::first();
        $this->assertCount(1, $event->patternMatchedProfiles);
        $this->assertEquals(0, $event->patternMatchedProfiles()->first()->pivot->is_profile_active);
    }
}
