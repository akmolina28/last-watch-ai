<?php

namespace Tests\Feature;

use App\DetectionEvent;
use App\DetectionProfile;
use App\WebRequestConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebRequestAutomationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function web_request_job_200()
    {
        $url = $this->faker->url;

        $config = WebRequestConfig::create([
            'url' => $url,
            'name' => 'test',
        ]);

        $profile = factory(DetectionProfile::class)->create();

        $profile->automations()->create([
            'automation_config_id' => $config->id,
            'automation_config_type' => $config->getTable(),
        ]);

        $event = factory(DetectionEvent::class)->create();

        Http::fake([
            $url => Http::response(['message' => 'OK.'], 200),
        ]);

        $result = $profile->automations()->first()->run($event, $profile);

        $this->assertNotNull($result);
        $this->assertEquals(false, $result->is_error);
        $this->assertEquals('{"message":"OK."}', $result->response_text);
    }

    /**
     * @test
     */
    public function web_request_job_404()
    {
        $url = $this->faker->url;

        $config = WebRequestConfig::create([
            'url' => $url,
            'name' => 'test',
        ]);

        $profile = factory(DetectionProfile::class)->create();

        $profile->automations()->create([
            'automation_config_id' => $config->id,
            'automation_config_type' => $config->getTable(),
        ]);

        $event = factory(DetectionEvent::class)->create();

        Http::fake([
            $url => Http::response(['message' => 'not found.'], 404),
        ]);

        $result = $profile->automations()->first()->run($event, $profile);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result->is_error);
        $this->assertEquals('{"message":"not found."}', $result->response_text);
    }
}
