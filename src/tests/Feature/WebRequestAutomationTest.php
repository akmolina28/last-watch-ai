<?php

namespace Tests\Feature;

use App\DetectionEvent;
use App\DetectionProfile;
use App\WebRequestConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Response;
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
            'name' => 'test'
        ]);

        $profile = factory(DetectionProfile::class)->create();

        $profile->automations()->create([
            'automation_config_id' => $config->id,
            'automation_config_type' => $config->getTable()
        ]);

        $event = factory(DetectionEvent::class)->create();

        $mockResponse = new Response(new \GuzzleHttp\Psr7\Response(200));

        Http::shouldReceive('get')
            ->once()
            ->with($url)
            ->andReturn($mockResponse);

        $result = $profile->automations()->first()->run($event, $profile);

        $this->assertNotNull($result);
        $this->assertEquals(0, $result->is_error);
    }


    /**
     * @test
     */
    public function web_request_job_404()
    {
        $url = $this->faker->url;

        $config = WebRequestConfig::create([
            'url' => $url,
            'name' => 'test'
        ]);

        $profile = factory(DetectionProfile::class)->create();

        $profile->automations()->create([
            'automation_config_id' => $config->id,
            'automation_config_type' => $config->getTable()
        ]);

        $event = factory(DetectionEvent::class)->create();

        $mockResponse = new Response(new \GuzzleHttp\Psr7\Response(404));

        Http::shouldReceive('get')
            ->once()
            ->with($url)
            ->andReturn($mockResponse);

        $result = $profile->automations()->first()->run($event, $profile);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result->is_error);
    }
}
