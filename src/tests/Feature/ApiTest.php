<?php

namespace Tests\Feature;

use App\AiPrediction;
use App\AutomationConfig;
use App\DeepstackCall;
use App\DetectionEvent;
use App\DetectionProfile;
use App\FolderCopyConfig;
use App\ImageFile;
use App\Jobs\EnableDetectionProfileJob;
use App\MqttPublishConfig;
use App\SmbCifsCopyConfig;
use App\TelegramConfig;
use App\WebRequestConfig;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiTest extends TestCase
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

    /**
     * @test
     */
    public function missing_api_routes_should_return_a_json_404()
    {
        $this->withoutExceptionHandling();
        $response = $this->withoutMiddleware()->get('/api/missing/route');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJson([
            'message' => 'Not Found.',
        ]);
    }

    /**
     * @test
     */
    public function api_can_get_all_profiles()
    {
        factory(DetectionProfile::class, 10)->create();

        $response = $this->get('/api/profiles');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'name',
                    'slug',
                    'file_pattern',
                    'object_classes',
                    'min_confidence',
                    'use_mask',
                    'use_regex',
                    'is_negative',
                    'use_smart_filter',
                    'smart_filter_precision',
                    'min_object_size',
                    'start_time',
                    'end_time',
                    'status',
                ]],
            ])
            ->assertJsonCount(10, 'data');
    }

    /**
     * @test
     */
    public function api_can_create_a_profile_without_a_mask()
    {
        $this->json('POST', '/api/profiles', [
            'name' => 'My Awesome Profile',
            'file_pattern' => 'camera123',
            'use_regex' => false,
            'object_classes' => '["car", "person"]',
            'min_confidence' => 0.42,
        ])
            ->assertStatus(201)
            ->assertJsonCount(1)
            ->assertJson([
                'data' => [
                    'name' => 'My Awesome Profile',
                    'slug' => 'my-awesome-profile',
                    'file_pattern' => 'camera123',
                    'use_mask' => false,
                    'object_classes' => [
                        0 => 'car',
                        1 =>'person',
                    ],
                    'min_confidence' => 0.42,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_create_a_profile_with_smart_filtering()
    {
        $this->json('POST', '/api/profiles', [
            'name' => 'My Awesome Profile',
            'file_pattern' => 'camera123',
            'use_regex' => 'false',
            'object_classes' => '["car", "person"]',
            'min_confidence' => 0.42,
            'use_smart_filter' => 'true',
            'smart_filter_precision' => 0.69,
        ])
            ->assertStatus(201)
            ->assertJsonCount(1)
            ->assertJson([
                'data' => [
                    'name' => 'My Awesome Profile',
                    'slug' => 'my-awesome-profile',
                    'file_pattern' => 'camera123',
                    'use_mask' => false,
                    'object_classes' => [
                        0 => 'car',
                        1 =>'person',
                    ],
                    'min_confidence' => 0.42,
                    'use_smart_filter' => true,
                    'smart_filter_precision' => 0.69,
                ],
            ]);

        $profile = DetectionProfile::first();

        $this->assertEquals(1, $profile->use_smart_filter);
        $this->assertEquals(0.69, $profile->smart_filter_precision);
    }

    /**
     * @test
     */
    public function api_can_create_a_negative_profile()
    {
        $this->json('POST', '/api/profiles', [
            'name' => 'My Awesome Profile',
            'file_pattern' => 'camera123',
            'use_regex' => 'false',
            'object_classes' => '["car", "person"]',
            'min_confidence' => 0.42,
            'use_smart_filter' => 'false',
            'is_negative' => 'true',
        ])
            ->assertStatus(201)
            ->assertJsonCount(1)
            ->assertJson([
                'data' => [
                    'name' => 'My Awesome Profile',
                    'is_negative' => true,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_delete_a_profile()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->json('DELETE', '/api/profiles/'.$profile->id)
            ->assertStatus(200);

        $profile->refresh();

        $this->assertTrue($profile->trashed());
    }

    /**
     * @test
     */
    public function api_can_reuse_name_of_deleted_profile()
    {
        $profileName = $this->faker->word();

        $profile = factory(DetectionProfile::class)->create([
            'name' => $profileName,
        ]);

        $profile->delete();

        $this->json('POST', '/api/profiles', [
            'name' => $profileName,
            'file_pattern' => 'camera123',
            'use_regex' => false,
            'object_classes' => '["car", "person"]',
            'min_confidence' => 0.42,
        ])
            ->assertStatus(201);
    }

    /**
     * @test
     */
    public function api_can_get_a_profile()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->get('/api/profiles/'.$profile->id)
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJson([
                'data' => [
                    'name' => $profile->name,
                    'slug' => $profile->slug,
                    'file_pattern' => $profile->file_pattern,
                ],
            ]);
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
            $prediction->detectionProfiles()->attach($profile->id, [
                'is_masked' => false,
            ]);
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
            $prediction->detectionProfiles()->attach($differentProfile->id, [
                'is_masked' => false,
            ]);
        }
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
            'is_masked' => false,
        ]);

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

        $response = $this->get('/api/events?relevant&profileId='.$profile->id);

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

        $response = $this->get('/api/events?profileId='.$profile->id);

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
    public function api_can_get_a_detection_event_with_no_matches()
    {
        $event = factory(DetectionEvent::class)->create();

        $this->get('/api/events/'.$event->id)
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

        $this->get('/api/events/'.$event->id)
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

        $this->get('/api/events/'.$event->id)
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

        $this->get('/api/events/'.$event->id)
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

        $this->get('/api/events/'.$event->id)
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

        $this->get('/api/events/'.$event->id)
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

        $this->get('/api/events/'.$event->id)
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
    public function api_can_get_telegram_configs()
    {
        factory(TelegramConfig::class, 5)->create();

        $this->get('/api/automations/telegram')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'name',
                    'token',
                    'chat_id',
                    'created_at',
                ]],
            ])
            ->assertJsonCount(5, 'data');
    }

    /**
     * @test
     */
    public function api_can_create_a_telegram_config()
    {
        $this->post('/api/automations/telegram', [
            'name' => 'My Bot',
            'token' => 'abc123wra8v7ar9e8wac987wac897ea98ce7w98f7ewa97f',
            'chat_id' => '1192051592',
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'My Bot',
                    'token' => 'abc123wra8v7ar9e8wac987wac897ea98ce7w98f7ewa97f',
                    'chat_id' => '1192051592',
                    'detection_profiles' => [],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_reuse_name_of_a_deleted_telegram_config()
    {
        $config = factory(TelegramConfig::class)->create(['name' => 'my unique config']);

        $config->delete();

        $this->json('POST', '/api/automations/telegram', [
            'name' => 'my unique config',
            'token' => 'asdfasdfasdf',
            'chat_id' => '15251252314',
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'my unique config',
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_web_request_configs()
    {
        factory(WebRequestConfig::class, 5)->create();

        $this->get('/api/automations/webRequest')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'name',
                    'url',
                ]],
            ])
            ->assertJsonCount(5, 'data');
    }

    /**
     * @test
     */
    public function api_can_get_web_request_configs_with_subscribed_profiles()
    {
        $profile1 = factory(DetectionProfile::class)->create();
        $profile2 = factory(DetectionProfile::class)->create();
        $profile3 = factory(DetectionProfile::class)->create();

        $config = factory(WebRequestConfig::class)->create();

        $profile1->subscribeAutomation(WebRequestConfig::class, $config->id);
        $profile2->subscribeAutomation(WebRequestConfig::class, $config->id);
        $profile3->subscribeAutomation(WebRequestConfig::class, $config->id);

        $this->json('GET', '/api/automations/webRequest')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    0 => [
                        'detection_profiles' => [
                            2 => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_create_a_web_request_config()
    {
        $this->post('/api/automations/webRequest', [
            'name' => 'Web Test',
            'url' => 'http://google.com',
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Web Test',
                    'url' => 'http://google.com',
                    'detection_profiles' => [],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_delete_a_web_request_config()
    {
        $config = factory(WebRequestConfig::class)->create();

        $this->json('DELETE', '/api/automations/webRequest/'.$config->id)
            ->assertStatus(200);

        $this->assertSoftDeleted($config);
    }

    /**
     * @test
     */
    public function api_can_delete_a_web_request_config_with_subscribers()
    {
        $config = factory(WebRequestConfig::class)->create();

        $profile = factory(DetectionProfile::class)->create();

        $profile->subscribeAutomation(WebRequestConfig::class, $config->id);

        $this->json('DELETE', '/api/automations/webRequest/'.$config->id)
            ->assertStatus(200);

        $this->assertSoftDeleted($config);
        $this->assertCount(0, $profile->automations);
    }

    /**
     * @test
     */
    public function api_can_get_mqtt_publish_configs()
    {
        factory(MqttPublishConfig::class, 5)->create();

        $this->get('/api/automations/mqttPublish')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'name',
                    'server',
                    'port',
                    'topic',
                    'client_id',
                    'qos',
                    'is_anonymous',
                    'username',
                    'password',
                    'payload_json',
                ]],
            ])
            ->assertJsonCount(5, 'data');
    }

    /**
     * @test
     */
    public function api_can_create_an_mqtt_publish_config()
    {
        $this->post('/api/automations/mqttPublish', [
            'name' => 'Mqtt Test',
            'server' => '127.0.0.1',
            'port' => '1883',
            'topic' => 'mqtt/foobar',
            'client_id' => 'unittest',
            'qos' => 2,
            'is_anonymous' => false,
            'username' => 'testuser',
            'password' => 'testpass',
            'payload_json' => '{"my":"payload"}',
            'is_custom_payload' => true,
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Mqtt Test',
                    'server' => '127.0.0.1',
                    'port' => '1883',
                    'topic' => 'mqtt/foobar',
                    'client_id' => 'unittest',
                    'qos' => 2,
                    'is_anonymous' => false,
                    'username' => 'testuser',
                    'password' => 'testpass',
                    'payload_json' => '{"my":"payload"}',
                    'is_custom_payload' => true,
                    'detection_profiles' => [],
                ],
            ]);

        $this->assertCount(1, MqttPublishConfig::all());
    }

    /**
     * @test
     */
    public function api_can_create_an_anonymous_mqtt_publish_config()
    {
        $this->post('/api/automations/mqttPublish', [
            'name' => 'Mqtt Test',
            'server' => '127.0.0.1',
            'port' => '1883',
            'topic' => 'mqtt/foobar',
            'client_id' => 'unittest',
            'qos' => 2,
            'is_anonymous' => true,
            'username' => '',
            'password' => '',
            'payload_json' => '{"my":"payload"}',
            'is_custom_payload' => true,
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Mqtt Test',
                    'server' => '127.0.0.1',
                    'port' => '1883',
                    'topic' => 'mqtt/foobar',
                    'client_id' => 'unittest',
                    'qos' => 2,
                    'is_anonymous' => true,
                    'username' => '',
                    'password' => '',
                    'payload_json' => '{"my":"payload"}',
                    'is_custom_payload' => true,
                    'detection_profiles' => [],
                ],
            ]);

        $this->assertCount(1, MqttPublishConfig::all());
    }

    /**
     * @test
     */
    public function api_can_get_folder_copy_configs()
    {
        factory(FolderCopyConfig::class, 5)->create();

        $this->get('/api/automations/folderCopy')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'name',
                    'copy_to',
                    'overwrite',
                ]],
            ])
            ->assertJsonCount(5, 'data');
    }

    /**
     * @test
     */
    public function api_can_create_a_folder_copy_config()
    {
        $this->post('/api/automations/folderCopy', [
            'name' => 'Folder Copy Test',
            'copy_to' => '/mnt/test',
            'overwrite' => true,
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Folder Copy Test',
                    'copy_to' => '/mnt/test',
                    'overwrite' => true,
                    'detection_profiles' => [],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_smb_cifs_copy_configs()
    {
        factory(SmbCifsCopyConfig::class, 5)->create();

        $this->get('/api/automations/smbCifsCopy')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'name',
                    'servicename',
                    'user',
                    'password',
                    'remote_dest',
                    'overwrite',
                ]],
            ])
            ->assertJsonCount(5, 'data');
    }

    /**
     * @test
     */
    public function api_can_create_a_smb_cifs_copy_config()
    {
        $this->post('/api/automations/smbCifsCopy', [
            'name' => 'Test Share',
            'servicename' => '//192.168.1.100/share',
            'user' => 'testuser',
            'password' => 'testpassword',
            'remote_dest' => '/path/to/dest',
            'overwrite' => true,
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Test Share',
                    'servicename' => '//192.168.1.100/share',
                    'user' => 'testuser',
                    'password' => 'testpassword',
                    'remote_dest' => '/path/to/dest',
                    'overwrite' => true,
                    'detection_profiles' => [],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_delete_a_telegram_config_with_subscribers()
    {
        $config = factory(TelegramConfig::class)->create();

        $profile = factory(DetectionProfile::class)->create();

        $profile->subscribeAutomation(TelegramConfig::class, $config->id);

        $this->json('DELETE', '/api/automations/telegram/'.$config->id)
            ->assertStatus(200);

        $this->assertSoftDeleted($config);
        $this->assertCount(0, $profile->automations);
    }

    /**
     * @test
     */
    public function api_can_delete_a_folder_copy_config_with_subscribers()
    {
        $config = factory(FolderCopyConfig::class)->create();

        $profile = factory(DetectionProfile::class)->create();

        $profile->subscribeAutomation(FolderCopyConfig::class, $config->id);

        $this->json('DELETE', '/api/automations/folderCopy/'.$config->id)
            ->assertStatus(200);

        $this->assertSoftDeleted($config);
        $this->assertCount(0, $profile->automations);
    }

    /**
     * @test
     */
    public function api_can_delete_a_smb_cifs_copy_config_with_subscribers()
    {
        $config = factory(SmbCifsCopyConfig::class)->create();

        $profile = factory(DetectionProfile::class)->create();

        $profile->subscribeAutomation(SmbCifsCopyConfig::class, $config->id);

        $this->json('DELETE', '/api/automations/smbCifsCopy/'.$config->id)
            ->assertStatus(200);

        $this->assertSoftDeleted($config);
        $this->assertCount(0, $profile->automations);
    }

    /**
     * @test
     */
    public function api_can_delete_a_mqtt_publish_config_with_subscribers()
    {
        $config = factory(MqttPublishConfig::class)->create();

        $profile = factory(DetectionProfile::class)->create();

        $profile->subscribeAutomation(MqttPublishConfig::class, $config->id);

        $this->json('DELETE', '/api/automations/mqttPublish/'.$config->id)
            ->assertStatus(200);

        $this->assertSoftDeleted($config);
        $this->assertCount(0, $profile->automations);
    }

    /**
     * @test
     */
    public function api_can_get_all_available_profile_automations()
    {
        $profile = factory(DetectionProfile::class)->create();

        factory(TelegramConfig::class, 3)->create();

        factory(WebRequestConfig::class, 5)->create();

        $this->json('GET', '/api/profiles/'.$profile->id.'/automations')
            ->assertStatus(200)
            ->assertJsonCount(8, 'data')
            ->assertJsonStructure([
                'data' => [
                    0 => [
                        'id',
                        'name',
                        'type',
                        'detection_profile_id',
                        'is_high_priority',
                    ],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_all_available_profile_automations_with_subscriptions()
    {
        $profile = factory(DetectionProfile::class)->create();

        factory(TelegramConfig::class, 3)->create();

        factory(WebRequestConfig::class, 5)->create();

        $highPriorityConfig = TelegramConfig::first();

        $profile->subscribeAutomation(TelegramConfig::class, $highPriorityConfig->id, true);

        $lowPriorityConfig = WebRequestConfig::first();

        $profile->subscribeAutomation(WebRequestConfig::class, $lowPriorityConfig->id);

        $response = $this->json('GET', '/api/profiles/'.$profile->id.'/automations')
            ->assertStatus(200);

        $response->assertJsonFragment([
            'detection_profile_id' => $profile->id,
            'type' => 'telegram_configs',
            'id' => $highPriorityConfig->id,
            'is_high_priority' => 1,
        ]);

        $response->assertJsonFragment([
            'detection_profile_id' => $profile->id,
            'type' => 'web_request_configs',
            'id' => $lowPriorityConfig->id,
            'is_high_priority' => 0,
        ]);
    }

    /**
     * @test
     */
    public function api_can_attach_a_telegram_automation()
    {
        $profile = factory(DetectionProfile::class)->create();

        $config = factory(TelegramConfig::class)->create();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'telegram_configs',
            'id' => $config->id,
            'value' => true,
        ])
            ->assertStatus(200);

        $this->assertCount(1, AutomationConfig::all());
        $this->assertEquals($config->id, AutomationConfig::first()->automation_config_id);
    }

    /**
     * @test
     */
    public function api_can_attach_an_mqtt_publish_automation()
    {
        $profile = factory(DetectionProfile::class)->create();

        $config = factory(MqttPublishConfig::class)->create();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'mqtt_publish_configs',
            'id' => $config->id,
            'value' => true,
        ])
            ->assertStatus(200);

        $this->assertCount(1, AutomationConfig::all());
        $this->assertEquals($config->id, AutomationConfig::first()->automation_config_id);
    }

    /**
     * @test
     */
    public function api_can_attach_a_web_request_automation_multiple_times()
    {
        $profile = factory(DetectionProfile::class)->create();

        $config = factory(WebRequestConfig::class)->create();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => true,
        ])
            ->assertStatus(200);

        $this->assertCount(1, AutomationConfig::all());
        $this->assertEquals($config->id, AutomationConfig::first()->automation_config_id);

        $this->json('PUT', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => true,
        ]);

        $this->assertCount(1, AutomationConfig::all());
        $this->assertEquals($config->id, AutomationConfig::first()->automation_config_id);
    }

    /**
     * @test
     */
    public function api_can_attach_a_high_priority_automation()
    {
        $profile = factory(DetectionProfile::class)->create();

        $config = factory(WebRequestConfig::class)->create();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => true,
            'is_high_priority' => true,
        ])
            ->assertStatus(200);

        $this->assertCount(1, AutomationConfig::all());

        $automationConfig = AutomationConfig::first();
        $this->assertEquals($config->id, $automationConfig->automation_config_id);
        $this->assertTrue($automationConfig->is_high_priority);
    }

    /**
     * @test
     */
    public function api_can_change_priority_of_an_automation()
    {
        $profile = factory(DetectionProfile::class)->create();

        $config = factory(WebRequestConfig::class)->create();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => true,
            'is_high_priority' => false,
        ])
            ->assertStatus(200);

        $this->assertCount(1, AutomationConfig::all());

        $automationConfig = AutomationConfig::first();
        $this->assertEquals($config->id, $automationConfig->automation_config_id);
        $this->assertFalse($automationConfig->is_high_priority);

        $this->json('PUT', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => true,
            'is_high_priority' => true,
        ])
            ->assertStatus(200);

        $this->assertCount(1, AutomationConfig::all());

        $automationConfig = AutomationConfig::first();
        $this->assertEquals($config->id, $automationConfig->automation_config_id);
        $this->assertTrue($automationConfig->is_high_priority);
    }

    /**
     * @test
     */
    public function api_can_detach_a_web_request_automation()
    {
        $profile = factory(DetectionProfile::class)->create();

        $config = factory(WebRequestConfig::class)->create();

        $profile->subscribeAutomation(WebRequestConfig::class, $config->id);

        $this->json('PUT', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => false,
        ])
            ->assertStatus(200);

        $this->assertCount(0, AutomationConfig::all());
        $this->assertCount(1, AutomationConfig::withTrashed()->get());
    }

    /**
     * @test
     */
    public function api_can_reattach_a_web_request_automation()
    {
        $profile = factory(DetectionProfile::class)->create();

        $config = factory(WebRequestConfig::class)->create();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => true,
        ])
            ->assertStatus(200);

        $this->json('PUT', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => false,
        ])
            ->assertStatus(200);

        $this->json('PUT', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => true,
        ])
            ->assertStatus(200);

        $this->assertCount(1, AutomationConfig::all());
        $this->assertEquals($config->id, AutomationConfig::first()->automation_config_id);
    }

    /**
     * @test
     */
    public function api_can_get_active_profile_status()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->get('/api/profiles/'.$profile->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'enabled',
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_inactive_profile_status()
    {
        $profile = factory(DetectionProfile::class)->create();
        $profile->is_enabled = false;
        $profile->save();

        $this->get('/api/profiles/'.$profile->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'disabled',
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_scheduled_profile_status()
    {
        $profile = factory(DetectionProfile::class)->create();
        $profile->is_scheduled = true;
        $profile->save();

        $this->get('/api/profiles/'.$profile->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'as_scheduled',
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_scheduled_profile_disabled_status()
    {
        $profile = factory(DetectionProfile::class)->create();
        $profile->is_scheduled = true;
        $profile->is_enabled = false;
        $profile->save();

        $this->get('/api/profiles/'.$profile->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'disabled',
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_set_profile_status_inactive()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/status', [
            'status' => 'disabled',
        ])
            ->assertStatus(204);

        $profile->refresh();

        $this->assertEquals('disabled', $profile->status);
    }

    /**
     * @test
     */
    public function api_can_set_profile_status_inactive_for_period()
    {
        Queue::fake();

        $profile = factory(DetectionProfile::class)->create();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/status', [
            'status' => 'disabled',
            'period' => 5,
        ])
            ->assertStatus(204);

        $profile->refresh();

        $this->assertEquals('disabled', $profile->status);

        Queue::assertPushed(
            EnableDetectionProfileJob::class,
            function ($job) use ($profile) {
                return $job->profile->id = $profile->id;
            }
        );
    }

    /**
     * @test
     */
    public function api_can_set_profile_status_active()
    {
        $profile = factory(DetectionProfile::class)->create();
        $profile->is_enabled = false;
        $profile->save();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/status', [
            'status' => 'enabled',
        ])
            ->assertStatus(204);

        $profile->refresh();

        $this->assertEquals('enabled', $profile->status);
    }

    /**
     * @test
     */
    public function api_can_set_profile_status_as_scheduled()
    {
        $profile = factory(DetectionProfile::class)->create();
        $profile->is_scheduled = true;
        $profile->save();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/status', [
            'status' => 'as_scheduled',
            'start_time' => '23:45',
            'end_time' => '12:34',
        ])
            ->assertStatus(204);

        $profile->refresh();

        $this->assertEquals('as_scheduled', $profile->status);
        $this->assertEquals('23:45', $profile->start_time);
        $this->assertEquals('12:34', $profile->end_time);
    }

    /**
     * @test
     */
    public function api_can_get_event_automation_errors()
    {
        $event = factory(DetectionEvent::class)->create();

        $telegramConfig = factory(TelegramConfig::class)->create();

        $profile = factory(DetectionProfile::class)->create();

        $config = AutomationConfig::create([
            'detection_profile_id' => $profile->id,
            'automation_config_type' => 'telegram_configs',
            'automation_config_id' => $telegramConfig->id,
        ]);

        $event->automationResults()->create([
            'automation_config_id' => $config->id,
            'is_error' => 1,
            'response_text' => 'testing123',
        ]);

        $this->json('GET', '/api/errors')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    0 => [
                        'is_error' => 1,
                        'response_text' => 'testing123',
                        'automation_config_id' => $config->id,
                        'automation_config' => [
                            'automation_config_type' => 'telegram_configs',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_deepstack_logs()
    {
        factory(DeepstackCall::class, 35)->create();

        $this->json('GET', '/api/deepstackLogs')
            ->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data' => [
                    0 => [
                        'id',
                        'input_file',
                        'created_at',
                        'called_at',
                        'returned_at',
                        'run_time_seconds',
                        'response_json',
                        'is_error',
                        'detection_event_id',
                    ],
                ],
            ]);
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

        $this->json('GET', '/api/events/'.$first->id.'/prev')
            ->assertStatus(404);

        $this->json('GET', '/api/events/'.$second->id.'/prev')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $first->id,
                ],
            ]);

        $this->json('GET', '/api/events/'.$third->id.'/prev')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $second->id,
                ],
            ]);

        $this->json('GET', '/api/events/'.$fourth->id.'/prev')
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

        $this->json('GET', '/api/events/'.$third->id.'/prev')
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
    public function api_can_get_profile_for_editing()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->json('GET', '/api/profiles/'.$profile->id.'/edit')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $profile->id,
                    'name' => $profile->name,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_profile_by_slug_name()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->json('GET', '/api/profiles/'.$profile->slug.'/edit')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $profile->id,
                    'name' => $profile->name,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_update_a_profile()
    {
        $profile = factory(DetectionProfile::class)->create([
            'name' => 'testing123',
            'file_pattern' => 'testing123',
            'use_regex' => false,
            'min_confidence' => 0.55,
            'object_classes' => ['person', 'car'],
            'use_smart_filter' => false,
            'smart_filter_precision' => 0.42,
            'min_object_size' => null,
        ]);

        $this->json('PATCH', '/api/profiles/'.$profile->id, [
            'id' => $profile->id,
            'name' => 'testing123',
            'file_pattern' => '/\btesting456\b/',
            'use_regex' => 'true',
            'object_classes' => '["dog", "cat"]',
            'min_confidence' => 0.69,
            'use_smart_filter' => 'true',
            'smart_filter_precision' => 0.77,
            'min_object_size' => 1234,
        ])
            ->assertJson([
                'data' => [
                    'name' => 'testing123',
                ],
            ])
            ->assertStatus(200);

        $profile->refresh();

        $this->assertEquals('testing123', $profile->name);
        $this->assertEquals('/\btesting456\b/', $profile->file_pattern);
        $this->assertTrue($profile->use_regex);
        $this->assertContains('dog', $profile->object_classes);
        $this->assertContains('cat', $profile->object_classes);
        $this->assertCount(2, $profile->object_classes);
        $this->assertTrue($profile->use_smart_filter);
        $this->assertEquals(0.69, $profile->min_confidence);
        $this->assertEquals(0.77, $profile->smart_filter_precision);
        $this->assertEquals(1234, $profile->min_object_size);
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
    public function api_can_get_event_image_file()
    {
        $imageFile = $this->createImageFile();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);

        $this->json('GET', '/api/events/'.$event->id.'/img')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'image/jpeg');
    }

    /**
     * @test
     */
    public function api_can_get_available_replacements()
    {
        $this->json('GET', '/api/automations/replacements')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '%image_file_name%',
                    '%profile_name%',
                    '%object_classes%',
                    '%event_url%',
                    '%image_url%',
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_is_alive()
    {
        $this->json('GET', '/api/alive')
            ->assertStatus(200);
    }

    /**
     * @test
     */
    public function api_throws_422_if_profile_status_update_invalid()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/status', [
            'status' => 'asdf',
        ])
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422);
    }

    /**
     * @test
     */
    public function api_throws_404_if_profile_status_update_invalid_id()
    {
        $this->json('PUT', '/api/profiles/999999/status', [
            'status' => 'asdf',
        ])
            ->assertStatus(404)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson([
                'message' => 'Not Found.',
            ]);
    }
}
