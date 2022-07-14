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

class EventsApiTest extends TestCase
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

    protected function add_latest_event(DetectionProfile $profile): DetectionEvent
    {
        // add a latest event
        $event = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::tomorrow(),
        ]);

        $event->aiPredictions()->createMany(
            factory(AiPrediction::class, 3)->make()->toArray()
        );

        $event->patternMatchedProfiles()->attach($profile->id);

        $prediction = $event->aiPredictions()->first();
        $prediction->detectionProfiles()->attach($profile->id, [
            'is_relevant' => true,
        ]);

        return $event;
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

    /**
     * @test
     */
    public function api_can_get_first_page_of_events()
    {
        factory(DetectionEvent::class, 30)->create();

        $response = $this->get('/api/events');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'image_file_name',
                    'detection_profiles_count',
                ]],
            ])
            ->assertJsonCount(10, 'data');
    }

    /**
     * @test
     */
    public function api_does_not_get_unprocessed_events()
    {
        factory(DetectionEvent::class, 5)->create();

        $this->get('/api/events')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');

        $event = DetectionEvent::first();
        $event->is_processed = false;
        $event->save();

        $this->get('/api/events')
            ->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    /**
     * @test
     */
    public function api_can_get_latest_relevant_event()
    {
        $profile = factory(DetectionProfile::class)->create();
        $this->setUpEvents($profile);

        $event = $this->add_latest_event($profile);

        $response = $this->get('/api/events/latest');

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $event->id,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_relevant_events()
    {
        $this->setUpEvents(factory(DetectionProfile::class)->create());

        $response = $this->get('/api/events?relevant');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'image_file_name',
                    'detection_profiles_count',
                ]],
            ])
            ->assertJsonCount(4, 'data');
    }

    /**
     * @test
     */
    public function api_can_get_relevant_events_by_profile()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->setUpEvents($profile);

        $response = $this->get('/api/events?relevant&profile=' . $profile->slug);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'image_file_name',
                    'detection_profiles_count',
                ]],
            ])
            ->assertJsonCount(2, 'data');
    }

    /**
     * @test
     */
    public function api_can_get_all_events_by_profile()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->setUpEvents($profile);

        $response = $this->get('/api/events?profile=' . $profile->slug);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'image_file_name',
                    'detection_profiles_count',
                ]],
            ])
            ->assertJsonCount(6, 'data');
    }



    /**
     * @test
     */
    public function api_can_get_relevant_events_by_group()
    {
        $profile_1 = factory(DetectionProfile::class)->create();
        $this->setUpEvents($profile_1);

        $profile_2 = factory(DetectionProfile::class)->create();
        $this->setUpEvents($profile_2);

        $group = factory(ProfileGroup::class)->create();
        $group->detectionProfiles()->save($profile_1);

        $response = $this->get('/api/events?relevant&group=' . $group->slug);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'image_file_name',
                    'detection_profiles_count',
                ]],
            ])
            ->assertJsonCount(2, 'data');
    }

    /**
     * @test
     */
    public function api_can_get_a_detection_event_with_no_matches()
    {
        $event = factory(DetectionEvent::class)->create();

        $this->get('/api/events/' . $event->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'image_file_name' => $event->image_file_name,
                    'detection_profiles_count' => 0,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_a_detection_event_with_automation_results()
    {
        $event = factory(DetectionEvent::class)->create();

        $config = factory(TelegramConfig::class)->create();

        $profile = factory(DetectionProfile::class)->create();

        $config = AutomationConfig::create([
            'detection_profile_id' => $profile->id,
            'automation_config_type' => 'telegram_configs',
            'automation_config_id' => $config->id,
        ]);

        $event->automationResults()->create([
            'automation_config_id' => $config->id,
            'is_error' => 0,
            'response_text' => 'testing123',
        ]);

        $this->get('/api/events/' . $event->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'automationResults' => [
                        0 => [
                            'is_error' => 0,
                            'response_text' => 'testing123',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_a_detection_event_with_matches()
    {
        $profiles = factory(DetectionProfile::class, 3)->create();
        $profile_ids = $profiles->pluck(['id']);

        $event = factory(DetectionEvent::class)->create();
        $event->patternMatchedProfiles()->attach($profile_ids);

        $this->get('/api/events/' . $event->id)
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.pattern_matched_profiles')
            ->assertJsonStructure([
                'data' => [
                    'image_file_name',
                    'image_height',
                    'image_width',
                    'detection_profiles_count',
                    'pattern_matched_profiles' => [
                        2 => [
                            'name',
                            'file_pattern',
                            'object_classes',
                            'is_profile_active',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_a_detection_event_deleted_profile_match()
    {
        $profiles = factory(DetectionProfile::class, 3)->create();
        $profile_ids = $profiles->pluck(['id']);

        $event = factory(DetectionEvent::class)->create();
        $event->patternMatchedProfiles()->attach($profile_ids);

        // delete a profile
        $profiles->first()->delete();

        $this->get('/api/events/' . $event->id)
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.pattern_matched_profiles')
            ->assertJsonStructure([
                'data' => [
                    'image_file_name',
                    'image_height',
                    'image_width',
                    'detection_profiles_count',
                    'pattern_matched_profiles' => [
                        2 => [
                            'name',
                            'file_pattern',
                            'object_classes',
                            'is_profile_active',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_a_detection_event_active_profile_match()
    {
        $profiles = factory(DetectionProfile::class, 3)->create();
        $profile_ids = $profiles->pluck(['id']);

        $event = factory(DetectionEvent::class)->create();
        $event->patternMatchedProfiles()->attach($profile_ids, [
            'is_profile_active' => true,
        ]);

        $this->get('/api/events/' . $event->id)
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.pattern_matched_profiles')
            ->assertJsonStructure([
                'data' => [
                    'image_file_name',
                    'image_height',
                    'image_width',
                    'detection_profiles_count',
                    'pattern_matched_profiles' => [
                        2 => [
                            'name',
                            'file_pattern',
                            'object_classes',
                            'is_profile_active',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'pattern_matched_profiles' => [
                        0 => [
                            'is_profile_active' => true,
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_a_detection_event_inactive_profile_match()
    {
        $profiles = factory(DetectionProfile::class, 3)->create();
        $profile_ids = $profiles->pluck(['id']);

        $event = factory(DetectionEvent::class)->create();
        $event->patternMatchedProfiles()->attach($profile_ids, [
            'is_profile_active' => false,
        ]);

        $this->get('/api/events/' . $event->id)
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.pattern_matched_profiles')
            ->assertJsonStructure([
                'data' => [
                    'image_file_name',
                    'image_height',
                    'image_width',
                    'detection_profiles_count',
                    'pattern_matched_profiles' => [
                        2 => [
                            'name',
                            'file_pattern',
                            'object_classes',
                            'is_profile_active',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'pattern_matched_profiles' => [
                        0 => [
                            'is_profile_active' => false,
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_a_detection_event_with_valid_image_url()
    {
        $imageFile = $this->createImageFile();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);

        $this->get('/api/events/' . $event->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'image_file_name' => 'testimage.jpg',
                    'image_file_path' => '/storage/events/testimage.jpg',
                ],
            ]);

        Storage::assertExists('events/testimage.jpg');
    }

    /**
     * @test
     */
    public function api_can_get_prev_event()
    {
        $profile = factory(DetectionProfile::class)->create();

        $first = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-5),
        ]);
        $first->patternMatchedProfiles()->attach($profile->id);

        $second = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-4),
        ]);
        $second->patternMatchedProfiles()->attach($profile->id);

        $third = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-3),
        ]);
        $third->patternMatchedProfiles()->attach($profile->id);

        $fourth = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-2),
        ]);
        $fourth->patternMatchedProfiles()->attach($profile->id);

        $this->json('GET', '/api/events/' . $first->id . '/prev')
            ->assertStatus(404);

        $this->json('GET', '/api/events/' . $second->id . '/prev')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $first->id,
                ],
            ]);

        $this->json('GET', '/api/events/' . $third->id . '/prev')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $second->id,
                ],
            ]);

        $this->json('GET', '/api/events/' . $fourth->id . '/prev')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $third->id,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_prev_event_for_profile()
    {
        $profile = factory(DetectionProfile::class)->create();

        $first = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-5),
        ]);
        $first->patternMatchedProfiles()->attach($profile->id);

        factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-4),
        ]);

        $third = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-3),
        ]);
        $third->patternMatchedProfiles()->attach($profile->id);

        $this->json('GET', '/api/events/' . $third->id . '/prev')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $first->id,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_event_image_file()
    {
        $imageFile = $this->createImageFile();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);

        $this->json('GET', '/api/events/' . $event->id . '/img')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'image/jpeg');
    }

    /**
     * @test
     */
    public function api_can_get_event_viewer_default()
    {
        $profile = factory(DetectionProfile::class)->create();
        $this->setUpEvents($profile);

        $profile_2 = factory(DetectionProfile::class)->create();
        $event = $this->add_latest_event($profile_2);

        $this->get('/api/events/viewer')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'image_file_name',
                    'image_file_path',
                    'image_width',
                    'image_height',
                    'thumbnail_path',
                    'ai_predictions',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $event->id,
                    'ai_predictions' => [0 => [
                        'detection_profiles' => [0 => [
                            'id' => $profile_2->id,
                            'slug' => $profile_2->slug,
                        ]],
                    ]],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_event_viewer_for_profile()
    {
        $profile = factory(DetectionProfile::class)->create();
        $this->setUpEvents($profile);

        $profile_2 = factory(DetectionProfile::class)->create();
        $event = $this->add_latest_event($profile_2);

        $this->get('/api/events/viewer?profile=' . $profile->slug)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'image_file_name',
                    'image_file_path',
                    'image_width',
                    'image_height',
                    'thumbnail_path',
                    'ai_predictions',
                ],
            ])
            ->assertJson([
                'data' => [
                    'ai_predictions' => [0 => [
                        'detection_profiles' => [0 => [
                            'id' => $profile->id,
                            'slug' => $profile->slug,
                        ]],
                    ]],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_event_viewer_for_event()
    {
        $profile = factory(DetectionProfile::class)->create();
        $this->setUpEvents($profile);

        $profile_2 = factory(DetectionProfile::class)->create();
        $event = $this->add_latest_event($profile_2);

        $this->get('/api/events/viewer?event=' . $event->id)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'image_file_name',
                    'image_file_path',
                    'image_width',
                    'image_height',
                    'thumbnail_path',
                    'ai_predictions',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $event->id,
                    'ai_predictions' => [0 => [
                        'detection_profiles' => [0 => [
                            'id' => $profile_2->id,
                            'slug' => $profile_2->slug,
                        ]],
                    ]],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_event_viewer_for_group()
    {
        $profile = factory(DetectionProfile::class)->create();
        $this->setUpEvents($profile);

        $profile_2 = factory(DetectionProfile::class)->create();
        $profile_2_event = $this->add_latest_event($profile_2);

        $profile_3 = factory(DetectionProfile::class)->create();
        $profile_3_event = $this->add_latest_event($profile_2);

        $group = ProfileGroup::create(['name' => 'test group']);
        $group->detectionProfiles()->save($profile);

        $this->get('/api/events/viewer?group=' . $group->slug)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'image_file_name',
                    'image_file_path',
                    'image_width',
                    'image_height',
                    'thumbnail_path',
                    'ai_predictions',
                ],
            ])
            ->assertJson([
                'data' => [
                    'ai_predictions' => [0 => [
                        'detection_profiles' => [0 => [
                            'id' => $profile->id,
                            'slug' => $profile->slug,
                        ]],
                    ]],
                ],
            ]);
    }
}
