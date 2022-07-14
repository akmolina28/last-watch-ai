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

class AutomationsApiTest extends TestCase
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

        $this->json('DELETE', '/api/automations/webRequest/' . $config->id)
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

        $this->json('DELETE', '/api/automations/webRequest/' . $config->id)
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

        $this->json('DELETE', '/api/automations/telegram/' . $config->id)
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

        $this->json('DELETE', '/api/automations/folderCopy/' . $config->id)
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

        $this->json('DELETE', '/api/automations/smbCifsCopy/' . $config->id)
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

        $this->json('DELETE', '/api/automations/mqttPublish/' . $config->id)
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

        $this->json('GET', '/api/profiles/' . $profile->id . '/automations')
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

        $response = $this->json('GET', '/api/profiles/' . $profile->id . '/automations')
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

        $this->json('PUT', '/api/profiles/' . $profile->id . '/automations', [
            'automations' => [
                [
                    'type' => 'telegram_configs',
                    'id' => $config->id,
                    'value' => true,
                    'is_high_priority' => false,
                ],
            ],
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

        $this->json('PUT', '/api/profiles/' . $profile->id . '/automations', [
            'automations' => [
                [
                    'type' => 'mqtt_publish_configs',
                    'id' => $config->id,
                    'value' => true,
                    'is_high_priority' => false,
                ],
            ],
        ])
            ->assertStatus(200);

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

        $this->json('PUT', '/api/profiles/' . $profile->id . '/automations', [
            'automations' => [
                [
                    'type' => 'web_request_configs',
                    'id' => $config->id,
                    'value' => true,
                    'is_high_priority' => true,
                ],
            ],
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

        $this->json('PUT', '/api/profiles/' . $profile->id . '/automations', [
            'automations' => [
                [
                    'type' => 'web_request_configs',
                    'id' => $config->id,
                    'value' => true,
                    'is_high_priority' => false,
                ],
            ],
        ])
            ->assertStatus(200);

        $this->assertCount(1, AutomationConfig::all());

        $automationConfig = AutomationConfig::first();
        $this->assertEquals($config->id, $automationConfig->automation_config_id);
        $this->assertFalse($automationConfig->is_high_priority);

        $this->json('PUT', '/api/profiles/' . $profile->id . '/automations', [
            'automations' => [
                [
                    'type' => 'web_request_configs',
                    'id' => $config->id,
                    'value' => true,
                    'is_high_priority' => true,
                ],
            ],
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

        $this->json('PUT', '/api/profiles/' . $profile->id . '/automations', [
            'automations' => [
                [
                    'type' => 'web_request_configs',
                    'id' => $config->id,
                    'value' => false,
                    'is_high_priority' => false,
                ],
            ],
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

        $this->json('PUT', '/api/profiles/' . $profile->id . '/automations', [
            'automations' => [
                [
                    'type' => 'web_request_configs',
                    'id' => $config->id,
                    'value' => true,
                    'is_high_priority' => false,
                ],
            ],
        ])
            ->assertStatus(200);

        $this->json('PUT', '/api/profiles/' . $profile->id . '/automations', [
            'automations' => [
                [
                    'type' => 'web_request_configs',
                    'id' => $config->id,
                    'value' => false,
                    'is_high_priority' => false,
                ],
            ],
        ])
            ->assertStatus(200);

        $this->json('PUT', '/api/profiles/' . $profile->id . '/automations', [
            'automations' => [
                [
                    'type' => 'web_request_configs',
                    'id' => $config->id,
                    'value' => true,
                    'is_high_priority' => false,
                ],
            ],
        ])
            ->assertStatus(200);

        $this->assertCount(1, AutomationConfig::all());
        $this->assertEquals($config->id, AutomationConfig::first()->automation_config_id);
    }
}
