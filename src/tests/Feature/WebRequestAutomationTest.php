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
    public function web_request_can_have_no_replacements()
    {
        $event = factory(DetectionEvent::class)->create();


        $config = factory(WebRequestConfig::class)->create([
            'url' => 'http://foobar.win/',
            'headers_json' => '{ "foo": "bar" }',
            'body_json' => '{ "baz": "bang" }',
        ]);


        $replacedUrl = $config->getUrlWithReplacements($event);
        $this->assertEquals('http://foobar.win/', $replacedUrl);

        $replacedHeaders = $config->getHeadersWithReplacements($event);
        $this->assertEquals(['foo' => 'bar'], $replacedHeaders);

        $replacedBody = $config->getBodyWithReplacements($event);
        $this->assertEquals(['baz' => 'bang'], $replacedBody);
    }

    /**
     * @test
     */
    public function web_request_can_replace_image_file_name()
    {
        $event = factory(DetectionEvent::class)->create([
            'image_file_name' => 'events/testimage.jpg',
        ]);

        $config = factory(WebRequestConfig::class)->create([
            'url' => 'http://foobar.win/?image=%image_file_name%',
            'headers_json' => '{ "foo": "%image_file_name%" }',
            'body_json' => '{ "baz": "storage/%image_file_name%" }',
        ]);

        $replacedUrl = $config->getUrlWithReplacements($event);
        $this->assertEquals('http://foobar.win/?image=events/testimage.jpg', $replacedUrl);

        $replacedHeaders = $config->getHeadersWithReplacements($event);
        $this->assertEquals(['foo' => 'events/testimage.jpg'], $replacedHeaders);

        $replacedBody = $config->getBodyWithReplacements($event);
        $this->assertEquals(['baz' => 'storage/events/testimage.jpg'], $replacedBody);
    }

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
