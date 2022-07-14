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

class DeepstackLogsApiTest extends TestCase
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
}
