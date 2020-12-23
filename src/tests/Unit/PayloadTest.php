<?php

namespace Tests\Unit;

use App\AiPrediction;
use App\DetectionEvent;
use App\DetectionProfile;
use App\PayloadHelper;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PayloadTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     */
    public function automation_payload_can_replace_event_url()
    {
        $event = factory(DetectionEvent::class)->create();
        $profile = factory(DetectionProfile::class)->create();
        $payload = '{"link"="%event_url%"}';

        $replaced = PayloadHelper::doReplacements($payload, $event, $profile);

        $this->assertEquals('{"link"="http://unit.test:9999/events/'.$event->id.'"}', $replaced);
    }

    /**
     * @test
     */
    public function automation_payload_can_replace_image_file_name()
    {
        $event = factory(DetectionEvent::class)->create();
        $profile = factory(DetectionProfile::class)->create();
        $payload = '{"image"="%image_file_name%"}';

        $replaced = PayloadHelper::doReplacements($payload, $event, $profile);

        $this->assertEquals('{"image"="'.$event->image_file_name.'"}', $replaced);
    }

    /**
     * @test
     */
    public function automation_payload_can_replace_profile_name()
    {
        $event = factory(DetectionEvent::class)->create();
        $profile = factory(DetectionProfile::class)->create();
        $payload = '{"profile"="%profile_name%"}';

        $replaced = PayloadHelper::doReplacements($payload, $event, $profile);

        $this->assertEquals('{"profile"="'.$profile->name.'"}', $replaced);
    }

    /**
     * @test
     */
    public function automation_payload_can_replace_object_classes()
    {
        $event = factory(DetectionEvent::class)->create();
        $profile = factory(DetectionProfile::class)->create([
            'object_classes' => ['car', 'person', 'truck'],
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

        $payload = '{"objects"="%object_classes%"}';

        $replaced = PayloadHelper::doReplacements($payload, $event, $profile);

        $this->assertEquals('{"objects"="bus,car,truck"}', $replaced);
    }

    /**
     * @test
     */
    public function automation_payload_can_replace_image_url()
    {
        $event = factory(DetectionEvent::class)->create([
            'image_file_name' => 'events/testimage.jpg'
        ]);
        $profile = factory(DetectionProfile::class)->create();
        $payload = '{"link"="%image_url%"}';

        $replaced = PayloadHelper::doReplacements($payload, $event, $profile);

        $this->assertEquals('{"link"="http://unit.test:9999/storage/events/testimage.jpg"}', $replaced);
    }
}
