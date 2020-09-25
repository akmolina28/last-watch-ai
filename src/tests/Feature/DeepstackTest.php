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

class DeepstackTest extends TestCase
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
        $file = $imageFile->storeAs('events','testimage.jpg', 'public');
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
    public function an_event_can_be_processed_with_deepstack() {
        $event = factory(DetectionEvent::class)->make();
        $event->image_file_name = 'testimage.jpg';
        $event->save();

        $job = new ProcessDetectionEventJob($event);
        $job->handle();

        $event = DetectionEvent::first();

        $this->assertNotNull($event->deepstack_response);
    }
}
