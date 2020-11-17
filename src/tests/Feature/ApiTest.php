<?php

namespace Tests\Feature;

use App\AiPrediction;
use App\AutomationConfig;
use App\DetectionEvent;
use App\DetectionProfile;
use App\FolderCopyConfig;
use App\SmbCifsCopyConfig;
use App\TelegramConfig;
use App\WebRequestConfig;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function missing_api_routes_should_return_a_json_404()
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/api/missing/route');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJson([
            'message' => 'Not Found.'
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
                'data' =>
                    [0 => [
                        'id',
                        'name',
                        'object_classes',
                        'min_confidence',
                        'use_mask',
                        'start_time',
                        'end_time',
                        'status'
                    ]]
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
            'object_classes[]' => ['car', 'person'],
            'min_confidence' => 0.42
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
                        1 =>'person'
                    ],
                    'min_confidence' => 0.42
                ]
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
            'use_regex' => false,
            'object_classes[]' => ['car', 'person'],
            'min_confidence' => 0.42,
            'use_smart_filter' => true,
            'smart_filter_precision' => 0.69
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
                        1 =>'person'
                    ],
                    'min_confidence' => 0.42,
                    'use_smart_filter' => true,
                    'smart_filter_precision' => 0.69
                ]
            ]);

        $profile = DetectionProfile::first();

        $this->assertEquals(1, $profile->use_smart_filter);
        $this->assertEquals(0.69, $profile->smart_filter_precision);
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
            'name' => $profileName
        ]);

        $profile->delete();

        $this->json('POST', '/api/profiles', [
            'name' => $profileName,
            'file_pattern' => 'camera123',
            'use_regex' => false,
            'object_classes[]' => ['car', 'person'],
            'min_confidence' => 0.42
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
                ]
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
                'is_masked' => false
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
                'is_masked' => true
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
                'is_smart_filtered' => true
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
                'is_masked' => false
            ]);
        }
    }

    /**
     * @test
     */
    public function api_can_first_page_of_events()
    {
        factory(DetectionEvent::class, 30)->create();

        $response = $this->get('/api/events');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' =>
                    [0 => [
                        'id',
                        'image_file_name',
                        'image_dimensions',
                        'detection_profiles_count'

                    ]]
            ])
            ->assertJsonCount(10, 'data');
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
            'occurred_at' => Date::tomorrow()
        ]);

        $event->aiPredictions()->createMany(
            factory(AiPrediction::class, 3)->make()->toArray()
        );

        $event->patternMatchedProfiles()->attach($profile->id);

        $prediction = $event->aiPredictions()->first();
        $prediction->detectionProfiles()->attach($profile->id, [
            'is_masked' => false
        ]);

        $response = $this->get('/api/events/latest');

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $event->id
                ]
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
                'data' =>
                    [0 => [
                        'id',
                        'image_file_name',
                        'image_dimensions',
                        'detection_profiles_count'
                    ]]
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
                'data' =>
                    [0 => [
                        'id',
                        'image_file_name',
                        'image_dimensions',
                        'detection_profiles_count'
                    ]]
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
                'data' =>
                    [0 => [
                        'id',
                        'image_file_name',
                        'image_dimensions',
                        'detection_profiles_count'
                    ]]
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
                    'image_dimensions' => $event->image_dimensions,
                    'detection_profiles_count' => 0
                ]
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
            'response_text' => 'testing123'
        ]);

        $this->get('/api/events/'.$event->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'automationResults' => [
                        0 => [
                            'is_error' => 0,
                            'response_text' => 'testing123'
                        ]
                    ]
                ]
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
                    'image_dimensions',
                    'detection_profiles_count',
                    'pattern_matched_profiles' => [
                        2 => [
                            'name',
                            'file_pattern',
                            'object_classes',
                            'is_profile_active'
                        ]
                    ]
                ]
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
                    'image_dimensions',
                    'detection_profiles_count',
                    'pattern_matched_profiles' => [
                        2 => [
                            'name',
                            'file_pattern',
                            'object_classes',
                            'is_profile_active'
                        ]
                    ]
                ]
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
            'is_profile_active' => true
        ]);

        $this->get('/api/events/'.$event->id)
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.pattern_matched_profiles')
            ->assertJsonStructure([
                'data' => [
                    'image_file_name',
                    'image_dimensions',
                    'detection_profiles_count',
                    'pattern_matched_profiles' => [
                        2 => [
                            'name',
                            'file_pattern',
                            'object_classes',
                            'is_profile_active'
                        ]
                    ],
                ]
            ])
            ->assertJson([
                'data' => [
                    'pattern_matched_profiles' => [
                        0 => [
                            'is_profile_active' => true
                        ]
                    ]
                ]
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
            'is_profile_active' => false
        ]);

        $this->get('/api/events/'.$event->id)
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.pattern_matched_profiles')
            ->assertJsonStructure([
                'data' => [
                    'image_file_name',
                    'image_dimensions',
                    'detection_profiles_count',
                    'pattern_matched_profiles' => [
                        2 => [
                            'name',
                            'file_pattern',
                            'object_classes',
                            'is_profile_active'
                        ]
                    ],
                ]
            ])
            ->assertJson([
                'data' => [
                    'pattern_matched_profiles' => [
                        0 => [
                            'is_profile_active' => false
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_a_detection_event_with_valid_image_url()
    {
        Storage::fake('public');
        $imageFile = UploadedFile::fake()->image('testimage.jpg', 640, 480)->size(128);
        $file = $imageFile->storeAs('events', 'testimage.jpg');

        $event = factory(DetectionEvent::class)->make();
        $event->image_file_name = $file;
        $event->save();

        $this->get('/api/events/'.$event->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'image_file_name' => 'events/testimage.jpg'
                ]
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
                'data' =>
                    [0 => [
                        'id',
                        'name',
                        'token',
                        'chat_id',
                        'created_at'
                    ]]
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
            'chat_id' => '1192051592'
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'My Bot',
                    'token' => 'abc123wra8v7ar9e8wac987wac897ea98ce7w98f7ewa97f',
                    'chat_id' => '1192051592'
                ]
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
                'data' =>
                    [0 => [
                        'id',
                        'name',
                        'url'
                    ]]
            ])
            ->assertJsonCount(5, 'data');
    }

    /**
     * @test
     */
    public function api_can_create_a_web_request_config()
    {
        $this->post('/api/automations/webRequest', [
            'name' => 'Web Test',
            'url' => 'http://google.com'
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Web Test',
                    'url' => 'http://google.com'
                ]
            ]);
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
                'data' =>
                    [0 => [
                        'id',
                        'name',
                        'copy_to',
                        'overwrite'
                    ]]
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
            'overwrite' => true
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Folder Copy Test',
                    'copy_to' => '/mnt/test',
                    'overwrite' => true
                ]
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
                'data' =>
                    [0 => [
                        'id',
                        'name',
                        'servicename',
                        'user',
                        'password',
                        'remote_dest',
                        'overwrite'
                    ]]
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
            'overwrite' => true
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Test Share',
                    'servicename' => '//192.168.1.100/share',
                    'user' => 'testuser',
                    'password' => 'testpassword',
                    'remote_dest' => '/path/to/dest',
                    'overwrite' => true
                ]
            ]);
    }

    /**
     * @test
     */
    public function api_can_attach_a_telegram_automation()
    {
        $profile = factory(DetectionProfile::class)->create();

        $config = factory(TelegramConfig::class)->create();

        $this->json('POST', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'telegram_configs',
            'id' => $config->id,
            'value' => true
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

        $this->json('POST', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => true
        ])
            ->assertStatus(200);

        $this->assertCount(1, AutomationConfig::all());
        $this->assertEquals($config->id, AutomationConfig::first()->automation_config_id);

        $this->json('POST', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => true
        ]);

        $this->assertCount(1, AutomationConfig::all());
        $this->assertEquals($config->id, AutomationConfig::first()->automation_config_id);
    }

    /**
     * @test
     */
    public function api_can_detach_a_web_request_automation()
    {
        $profile = factory(DetectionProfile::class)->create();

        $config = factory(WebRequestConfig::class)->create();

        AutomationConfig::create([
            'detection_profile_id' => $profile->id,
            'automation_config_type' => 'web_request_configs',
            'automation_config_id' => $config->id
        ]);

        $this->json('POST', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => false
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

        $this->json('POST', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => true
        ])
            ->assertStatus(200);

        $this->json('POST', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => false
        ])
            ->assertStatus(200);

        $this->json('POST', '/api/profiles/'.$profile->id.'/automations', [
            'type' => 'web_request_configs',
            'id' => $config->id,
            'value' => true
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
                    'status' => 'enabled'
                ]
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
                    'status' => 'disabled'
                ]
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
                    'status' => 'as_scheduled'
                ]
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
                    'status' => 'disabled'
                ]
            ]);
    }

    /**
     * @test
     */
    public function api_can_set_profile_status_inactive()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->json('PUT', '/api/profiles/'.$profile->id.'/status', [
            'status' => 'disabled'
        ])
            ->assertStatus(204);

        $profile->refresh();

        $this->assertEquals('disabled', $profile->status);
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
            'status' => 'enabled'
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
            'end_time' => '12:34'
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
            'response_text' => 'testing123'
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
                            'automation_config_type' => 'telegram_configs'
                        ]
                    ]
                ]
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
                    'total_errors' => 0
                ]
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
                    'total_errors' => 0
                ]
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_prev_event()
    {
        $profile = factory(DetectionProfile::class)->create();

        $first = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-5)
        ]);
        $first->patternMatchedProfiles()->attach($profile->id);

        $second = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-4)
        ]);
        $second->patternMatchedProfiles()->attach($profile->id);

        $third = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-3)
        ]);
        $third->patternMatchedProfiles()->attach($profile->id);

        $fourth = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-2)
        ]);
        $fourth->patternMatchedProfiles()->attach($profile->id);

        $this->json('GET', '/api/events/'.$first->id.'/prev')
            ->assertStatus(404);

        $this->json('GET', '/api/events/'.$second->id.'/prev')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $first->id
                ]
            ]);

        $this->json('GET', '/api/events/'.$third->id.'/prev')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $second->id
                ]
            ]);

        $this->json('GET', '/api/events/'.$fourth->id.'/prev')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $third->id
                ]
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_prev_event_for_profile()
    {
        $profile = factory(DetectionProfile::class)->create();

        $first = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-5)
        ]);
        $first->patternMatchedProfiles()->attach($profile->id);

        factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-4)
        ]);

        $third = factory(DetectionEvent::class)->create([
            'occurred_at' => Date::now()->addHours(-3)
        ]);
        $third->patternMatchedProfiles()->attach($profile->id);

        $this->json('GET', '/api/events/'.$third->id.'/prev')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $first->id
                ]
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
                    'name' => $profile->name
                ]
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
            'smart_filter_precision' => 0.42
        ]);

        $this->json('PATCH', '/api/profiles/'.$profile->id, [
            'id' => $profile->id,
            'name' => 'testing123',
            'file_pattern' => '/\btesting456\b/',
            'use_regex' => true,
            'object_classes[]' => ['dog', 'cat'],
            'min_confidence' => 0.69,
            'use_smart_filter' => true,
            'smart_filter_precision' => 0.77
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'testing123'
                ]
            ]);

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
            'status' => 'asdf'
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
            'status' => 'asdf'
        ])
            ->assertStatus(404)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson([
                'message' => 'Not Found.'
            ]);
    }
}
