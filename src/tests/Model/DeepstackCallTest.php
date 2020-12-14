<?php

namespace Tests\Model;

use App\DeepstackCall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DeepstackCallTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */
    public function a_deepstack_call_can_have_a_run_time()
    {
        $diff = 3;
        $calledAt = Carbon::now();
        $returnedAt = clone $calledAt;
        $returnedAt->addSeconds($diff);

        $deepstackCall = factory(DeepstackCall::class)->create([
            'called_at' => $calledAt,
            'returned_at' => $returnedAt,
        ]);

        $this->assertEquals($diff, $deepstackCall->runTimeSeconds);
    }

    /**
     * @test
     */
    public function a_deepstack_call_can_have_predictions()
    {
        // @codingStandardsIgnoreLine
        $responseJson = '{"success":true,"predictions":[{"confidence":0.9995428,"label":"person","y_min":95,"x_min":295,"y_max":523,"x_max":451},{"confidence":0.9994912,"label":"person","y_min":99,"x_min":440,"y_max":531,"x_max":608},{"confidence":0.9990447,"label":"dog","y_min":358,"x_min":647,"y_max":539,"x_max":797}]}';

        $call = factory(DeepstackCall::class)->create([
            'response_json' => $responseJson,
        ]);

        $this->assertCount(3, $call->predictions);
    }
}
