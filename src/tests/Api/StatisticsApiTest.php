<?php

namespace Tests\Api;

use App\AiPrediction;
use App\AutomationConfig;
use App\DeepstackCall;
use App\DetectionEvent;
use App\DetectionProfile;
use App\FolderCopyConfig;
use App\ImageFile;
use App\Jobs\EnableDetectionProfileJob;
use App\MqttPublishConfig;
use App\ProfileGroup;
use App\SmbCifsCopyConfig;
use App\TelegramConfig;
use App\User;
use App\WebRequestConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StatisticsApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(
            ThrottleRequests::class
        );

        $user = new User(['name' => 'Administrator']);
        $this->be($user);
    }

    protected function setUpEvents(DetectionProfile $profile)
    {
        // make 3 unmatched, irrelevant events
        factory(DetectionEvent::class, 3)
            ->create()
            ->each(function ($event) {
                $event->aiPredictions()->createMany(
                    factory(AiPrediction::class, 3)->make()->toArray()
                );
            });

        // make 2 events matched and relevant
        $events = factory(DetectionEvent::class, 2)
            ->create()
            ->each(function ($event) use ($profile) {
                $event->aiPredictions()->createMany(
                    factory(AiPrediction::class, 3)->make()->toArray()
                );
                $event->patternMatchedProfiles()->attach($profile->id);
            });

        foreach ($events as $event) {
            $prediction = $event->aiPredictions()->first();
            $prediction->detectionProfiles()->attach($profile->id);
        }

        // make 1 event relevant but masked
        $events = factory(DetectionEvent::class, 1)
            ->create()
            ->each(function ($event) use ($profile) {
                $event->aiPredictions()->createMany(
                    factory(AiPrediction::class, 3)->make()->toArray()
                );
                $event->patternMatchedProfiles()->attach($profile->id);
            });

        foreach ($events as $event) {
            $prediction = $event->aiPredictions()->first();
            $prediction->detectionProfiles()->attach($profile->id, [
                'is_masked' => true,
                'is_relevant' => false,
            ]);
        }

        // make 1 event relevant but smart-filtered
        $events = factory(DetectionEvent::class, 1)
            ->create()
            ->each(function ($event) use ($profile) {
                $event->aiPredictions()->createMany(
                    factory(AiPrediction::class, 3)->make()->toArray()
                );
                $event->patternMatchedProfiles()->attach($profile->id);
            });

        foreach ($events as $event) {
            $prediction = $event->aiPredictions()->first();
            $prediction->detectionProfiles()->attach($profile->id, [
                'is_smart_filtered' => true,
                'is_relevant' => false,
            ]);
        }

        // make 2 events matched but not relevant
        factory(DetectionEvent::class, 2)
            ->create()
            ->each(function ($event) use ($profile) {
                $event->aiPredictions()->createMany(
                    factory(AiPrediction::class, 3)->make()->toArray()
                );
                $event->patternMatchedProfiles()->attach($profile->id);
            });

        // make 2 events matched and relevant to a different profile
        $differentProfile = factory(DetectionProfile::class)->create();

        $events = factory(DetectionEvent::class, 2)
            ->create()
            ->each(function ($event) use ($differentProfile) {
                $event->aiPredictions()->createMany(
                    factory(AiPrediction::class, 3)->make()->toArray()
                );
                $event->patternMatchedProfiles()->attach($differentProfile->id);
            });

        foreach ($events as $event) {
            $prediction = $event->aiPredictions()->first();
            $prediction->detectionProfiles()->attach($differentProfile->id);
        }
    }

    /**
     * @test
     */
    public function api_can_get_home_page_statistics_24_hrs()
    {
        $profile = factory(DetectionProfile::class)->create();
        $this->setUpEvents($profile);

        // put all events within the last 24 hours
        foreach (DetectionEvent::all() as $event) {
            $event->occurred_at = Date::now()->addHours(-1);
            $event->save();
        }

        $response = $this->get('/api/statistics');

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'relevant_events' => 4,
                    'total_events' => 11,
                    'total_errors' => 0,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_home_page_statistics_24_hrs_empty()
    {
        $profile = factory(DetectionProfile::class)->create();
        $this->setUpEvents($profile);

        // put all events outside the last 24 hours
        foreach (DetectionEvent::all() as $event) {
            $event->occurred_at = Date::now()->addHours(-36);
            $event->save();
        }

        $response = $this->get('/api/statistics');

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'relevant_events' => 0,
                    'total_events' => 0,
                    'total_errors' => 0,
                ],
            ]);
    }
}
