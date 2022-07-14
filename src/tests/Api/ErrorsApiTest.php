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

class ErrorsApiTest extends TestCase
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
}
