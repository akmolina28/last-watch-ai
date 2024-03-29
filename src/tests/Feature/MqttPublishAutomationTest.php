<?php

namespace Tests\Feature;

use App\DetectionEvent;
use App\DetectionProfile;
use App\ImageFile;
use App\MqttPublishConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class MqttPublishAutomationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    // use WithoutMiddleware;

    /**
     * @test
     */
    public function mqtt_automation_can_have_no_replacements()
    {
        $imageFile = factory(ImageFile::class)->create();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile,
        ]);

        $profile = factory(DetectionProfile::class)->create();

        $config = factory(MqttPublishConfig::class)->create();

        $payload = $config->getPayload($event, $profile);

        $this->assertEquals($payload, '{"foo":"bar"}');
    }

    /**
     * @test
     */
    public function mqtt_automation_can_have_replacements()
    {
        $imageFile = factory(ImageFile::class)->create();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile,
        ]);

        $profile = factory(DetectionProfile::class)->create([
            'name' => 'my awesome profile',
        ]);

        $config = factory(MqttPublishConfig::class)->create([
            'payload_json' => '{"foo":"%profile_name%"}',
        ]);

        $payload = $config->getPayload($event, $profile);

        $this->assertEquals($payload, '{"foo":"my awesome profile"}');
    }
}
