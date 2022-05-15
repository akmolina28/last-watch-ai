<?php

namespace Tests\Feature;

use App\AiPrediction;
use App\AutomationConfig;
use App\DeepstackCall;
use App\DetectionEvent;
use App\DetectionEventAutomationResult;
use App\DetectionProfile;
use App\ImageFile;
use App\User;
use App\Jobs\DeleteEventImageJob;
use App\Tasks\DeleteDetectionEventsTask;
use App\WebRequestConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class DeleteDetectionEventTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    // use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();

        $user = new User(['name' => 'Administrator']);
        $this->be($user);
    }

    protected function createImageFile($fileName = 'testimage.jpg', $thumbFileName = 'testimage-thumb.jpg'): ImageFile
    {
        $imageFile = UploadedFile::fake()->image($fileName, 640, 480)->size(128);
        $path = Storage::putFileAs('events', $imageFile, $fileName);

        $thumbFile = UploadedFile::fake()->image($fileName, 100, 100)->size(24);
        Storage::putFileAs('events', $thumbFile, $thumbFileName);

        return ImageFile::create([
            'path' => $path,
            'file_name' => $fileName,
            'width' => 640,
            'height' => 480,
        ]);
    }

    /**
     * @test
     */
    public function can_delete_detection_event_with_deepstack_calls()
    {
        factory(DeepstackCall::class)->create();

        $event = DetectionEvent::first();
        $event->delete();

        $this->assertCount(0, DetectionEvent::get());
        $this->assertCount(0, DeepstackCall::get());
    }

    /**
     * @test
     */
    public function can_delete_detection_event_with_predictions()
    {
        $event = factory(DetectionEvent::class)
            ->create();
        $event->aiPredictions()->createMany(
            factory(AiPrediction::class, 3)->make()->toArray()
        )
            ->each(function ($prediction) {
                $prediction->detectionProfiles()->attach(
                    factory(DetectionProfile::class)->create()
                );
            });

        $this->assertCount(1, DetectionEvent::get());
        $this->assertCount(3, AiPrediction::get());
        $this->assertCount(3, DB::table('ai_prediction_detection_profile')->get());
        $this->assertCount(3, DetectionProfile::get());

        $event->delete();

        // the predictions should be deleted but the profiles should remain
        $this->assertCount(0, DetectionEvent::get());
        $this->assertCount(0, AiPrediction::get());
        $this->assertCount(0, DB::table('ai_prediction_detection_profile')->get());
        $this->assertCount(3, DetectionProfile::get());
    }

    /**
     * @test
     */
    public function can_delete_detection_event_with_pattern_matches()
    {
        $event = factory(DetectionEvent::class)
            ->create();
        $event->patternMatchedProfiles()->createMany(
            factory(DetectionProfile::class, 3)->make()->toArray()
        );

        $this->assertCount(1, DetectionEvent::get());
        $this->assertCount(3, DB::table('pattern_match')->get());
        $this->assertCount(3, DetectionProfile::get());

        $event->delete();

        // event and relations get deleted, profiles remain
        $this->assertCount(0, DetectionEvent::get());
        $this->assertCount(0, DB::table('pattern_match')->get());
        $this->assertCount(3, DetectionProfile::get());
    }

    /**
     * @test
     */
    public function can_delete_detection_event_with_automation_results()
    {
        // set up a profile automation config
        $config = factory(WebRequestConfig::class)->create();
        $profile = factory(DetectionProfile::class)->create();

        $this->put('/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'value' => 'true',
            'id' => $config->id,
        ])->assertStatus(200);

        // create an event and attach an automation result
        $automationConfig = AutomationConfig::first();
        $event = factory(DetectionEvent::class)->create();

        DetectionEventAutomationResult::create([
            'detection_event_id' => $event->id,
            'automation_config_id' => $automationConfig->id,
            'is_error' => false,
            'response_text' => 'OK',
        ]);

        // assert new records are in place
        $this->assertCount(1, DetectionEvent::get());
        $this->assertCount(1, DetectionProfile::get());
        $this->assertCount(1, AutomationConfig::get());
        $this->assertCount(1, DetectionEventAutomationResult::get());

        // delete the event
        $event->delete();

        // event relationships should be gone but profile and automation config should remain
        $this->assertCount(0, DetectionEvent::get());
        $this->assertCount(1, DetectionProfile::get());
        $this->assertCount(1, AutomationConfig::get());
        $this->assertCount(0, DetectionEventAutomationResult::get());
    }

    /**
     * @test
     */
    public function scheduled_task_can_delete_range_of_events()
    {
        for ($i = 0; $i < 14; $i++) {
            factory(DetectionEvent::class)->create([
                'occurred_at' => Date::now()->addDays(-$i),
            ]);
        }

        $this->assertCount(14, DetectionEvent::get());

        DeleteDetectionEventsTask::run(7);

        $this->assertCount(8, DetectionEvent::get());

        // first occurence is 7 days ago
        $this->assertLessThan(Date::now()->addDays(-7), new Carbon(DetectionEvent::min('occurred_at')));
        $this->assertGreaterThan(Date::now()->addDays(-8), new Carbon(DetectionEvent::min('occurred_at')));
    }

    /**
     * @test
     */
    public function delete_image_job_can_delete_image_and_thumbnail()
    {
        $imageFile = $this->createImageFile();

        $filePath = $imageFile->getPath();
        $thumbnailPath = $imageFile->getPath(true);

        Storage::assertExists($filePath);
        Storage::assertExists($thumbnailPath);

        $job = new DeleteEventImageJob($imageFile);

        $job->handle();

        Storage::assertMissing($filePath);
        Storage::assertMissing($thumbnailPath);

        $this->assertDeleted($imageFile);
    }

    /**
     * @test
     */
    public function scheduled_task_can_delete_events_with_image_files()
    {
        Queue::fake();

        $imageFile1 = $this->createImageFile('test-image-01.jpg', 'test-image-01-thumb.jpg');
        $imageFile2 = $this->createImageFile('test-image-02.jpg', 'test-image-02-thumb.jpg');
        $imageFile3 = $this->createImageFile('test-image-03.jpg', 'test-image-03-thumb.jpg');

        Storage::assertExists($imageFile1->getPath());
        Storage::assertExists($imageFile1->getPath(true));
        Storage::assertExists($imageFile2->getPath());
        Storage::assertExists($imageFile2->getPath(true));
        Storage::assertExists($imageFile3->getPath());
        Storage::assertExists($imageFile3->getPath(true));

        // create three 10-day-old events
        factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile1->id,
            'occurred_at' => Date::now()->addDays(-10),
        ]);

        factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile2->id,
            'occurred_at' => Date::now()->addDays(-10),
        ]);

        factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile3->id,
            'occurred_at' => Date::now()->addDays(-10),
        ]);

        $this->assertCount(3, DetectionEvent::get());

        // delete all of the events
        DeleteDetectionEventsTask::run(5);

        $this->assertCount(0, DetectionEvent::get());

        // make sure deletion jobs were queued
        Queue::assertPushedOn('low', DeleteEventImageJob::class, function ($job) use ($imageFile1) {
            return $job->imageFile->id === $imageFile1->id;
        });

        Queue::assertPushedOn('low', DeleteEventImageJob::class, function ($job) use ($imageFile2) {
            return $job->imageFile->id === $imageFile2->id;
        });

        Queue::assertPushedOn('low', DeleteEventImageJob::class, function ($job) use ($imageFile3) {
            return $job->imageFile->id === $imageFile3->id;
        });
    }
}
