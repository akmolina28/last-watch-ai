<?php

namespace Tests\Feature;

use App\AiPrediction;
use App\DetectionEvent;
use App\DetectionProfile;
use App\Exceptions\AutomationException;
use App\ImageFile;
use App\WebRequestConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class WebRequestAutomationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    // use WithoutMiddleware;

    /**
     * @test
     */
    public function web_request_can_have_no_replacements()
    {
        $imageFile = factory(ImageFile::class)->create();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile,
        ]);

        $profile = factory(DetectionProfile::class)->create();

        $config = factory(WebRequestConfig::class)->create([
            'url' => 'http://foobar.win/',
            'headers_json' => '{ "foo": "bar" }',
            'body_json' => '{ "baz": "bang" }',
        ]);

        $replacedUrl = $config->getUrlWithReplacements($event, $profile);
        $this->assertEquals('http://foobar.win/', $replacedUrl);

        $replacedHeaders = $config->getHeadersWithReplacements($event, $profile);
        $this->assertEquals(['foo' => 'bar'], $replacedHeaders);

        $replacedBody = $config->getBodyWithReplacements($event, $profile);
        $this->assertEquals(['baz' => 'bang'], $replacedBody);
    }

    /**
     * @test
     */
    public function web_request_can_replace_image_file_name()
    {
        $profile = factory(DetectionProfile::class)->create();

        $imageFile = factory(ImageFile::class)->create();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile,
        ]);

        $config = factory(WebRequestConfig::class)->create([
            'url' => 'http://foobar.win/?image=%image_file_name%',
            'headers_json' => '{ "foo": "%image_file_name%" }',
            'body_json' => '{ "baz": "storage/%image_file_name%" }',
        ]);

        $replacedUrl = $config->getUrlWithReplacements($event, $profile);
        $this->assertEquals('http://foobar.win/?image=testimage.jpg', $replacedUrl);

        $replacedHeaders = $config->getHeadersWithReplacements($event, $profile);
        $this->assertEquals(['foo' => 'testimage.jpg'], $replacedHeaders);

        $replacedBody = $config->getBodyWithReplacements($event, $profile);
        $this->assertEquals(['baz' => 'storage/testimage.jpg'], $replacedBody);
    }

    /**
     * @test
     */
    public function web_request_can_replace_object_classes()
    {
        $profile = factory(DetectionProfile::class)->create();

        $imageFile = factory(ImageFile::class)->create();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile,
        ]);

        $carPrediction = factory(AiPrediction::class)->create([
            'object_class' => 'car',
            'detection_event_id' => $event->id,
        ]);
        $profile->aiPredictions()->attach($carPrediction->id);

        $truckPrediction = factory(AiPrediction::class)->create([
            'object_class' => 'truck',
            'detection_event_id' => $event->id,
        ]);
        $profile->aiPredictions()->attach($truckPrediction->id);

        $busPrediction = factory(AiPrediction::class)->create([
            'object_class' => 'bus',
            'detection_event_id' => $event->id,
        ]);
        $profile->aiPredictions()->attach($busPrediction->id);

        $config = factory(WebRequestConfig::class)->create([
            'url' => 'http://foobar.win/?objects=%object_classes%',
            'headers_json' => '{ "foo": "%object_classes%" }',
            'body_json' => '{ "baz": "%object_classes%" }',
        ]);

        $replacedUrl = $config->getUrlWithReplacements($event, $profile);
        $this->assertEquals('http://foobar.win/?objects=bus,car,truck', $replacedUrl);

        $replacedHeaders = $config->getHeadersWithReplacements($event, $profile);
        $this->assertEquals(['foo' => 'bus,car,truck'], $replacedHeaders);

        $replacedBody = $config->getBodyWithReplacements($event, $profile);
        $this->assertEquals(['baz' => 'bus,car,truck'], $replacedBody);
    }

    /**
     * @test
     */
    public function web_request_can_replace_profile_name()
    {
        $profile = factory(DetectionProfile::class)->create([
            'name' => 'My Awesome Profile',
        ]);

        $imageFile = factory(ImageFile::class)->create();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile,
        ]);

        $config = factory(WebRequestConfig::class)->create([
            'url' => 'http://foobar.win/?profile=%profile_name%',
            'headers_json' => '{ "foo": "%profile_name%" }',
            'body_json' => '{ "baz": "profile is %profile_name%" }',
        ]);

        $replacedUrl = $config->getUrlWithReplacements($event, $profile);
        $this->assertEquals('http://foobar.win/?profile=My Awesome Profile', $replacedUrl);

        $replacedHeaders = $config->getHeadersWithReplacements($event, $profile);
        $this->assertEquals(['foo' => 'My Awesome Profile'], $replacedHeaders);

        $replacedBody = $config->getBodyWithReplacements($event, $profile);
        $this->assertEquals(['baz' => 'profile is My Awesome Profile'], $replacedBody);
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

        $imageFile = factory(ImageFile::class)->create();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile,
        ]);

        Http::fake([
            $url => Http::response(['message' => 'OK.'], 200),
        ]);

        $result = $profile->automations()->first()->run($event, $profile);

        $this->assertTrue($result);
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

        $imageFile = factory(ImageFile::class)->create();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile,
        ]);

        Http::fake([
            $url => Http::response(['message' => 'not found.'], 404),
        ]);

        $this->expectException(AutomationException::class);

        $profile->automations()->first()->run($event, $profile);
    }
}
