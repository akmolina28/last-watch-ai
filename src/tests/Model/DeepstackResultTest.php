<?php

namespace Tests\Unit;

use App\DeepstackResult;
use Tests\TestCase;

class DeepstackResultTest extends TestCase
{
    /**
     * @test
     */
    public function a_deepstack_result_can_be_successful()
    {
        $deepstack_response = '{"success":true,"predictions":[{"confidence":0.9759266,"label":"car","y_min":47,"x_min":328,"y_max":105,"x_max":434},{"confidence":0.9018611,"label":"car","y_min":77,"x_min":125,"y_max":154,"x_max":190}]}';

        $result = new DeepstackResult($deepstack_response);

        $this->assertTrue($result->success);
    }

    /**
     * @test
     */
    public function a_deepstack_result_can_have_predictions()
    {
        $deepstack_response = '{"success":true,"predictions":[{"confidence":0.9759266,"label":"car","y_min":47,"x_min":328,"y_max":105,"x_max":434},{"confidence":0.9018611,"label":"car","y_min":77,"x_min":125,"y_max":154,"x_max":190}]}';

        $result = new DeepstackResult($deepstack_response);

        $this->assertNotEmpty($result->predictions);
    }
}
