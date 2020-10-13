<?php

namespace Tests\Unit;

use App\AiPrediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\DetectionProfile;

class AiPredictionTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */
    public function a_prediction_can_compute_its_area()
    {
        $predictionA = new AiPrediction();
        $predictionA->x_min = 0;
        $predictionA->y_min = 0;
        $predictionA->x_max = 100;
        $predictionA->y_max = 100;

        $this->assertEquals(10000, $predictionA->area());
    }

    /**
     * @test
     */
    public function two_predictions_can_overlap_100_percent()
    {
        $predictionA = new AiPrediction();
        $predictionA->x_min = 0;
        $predictionA->y_min = 0;
        $predictionA->x_max = 100;
        $predictionA->y_max = 100;

        $predictionB = new AiPrediction();
        $predictionB->x_min = 0;
        $predictionB->y_min = 0;
        $predictionB->x_max = 100;
        $predictionB->y_max = 100;

        $this->assertEquals(1, $predictionA->percentageOverlap($predictionB));
        $this->assertEquals(1, $predictionB->percentageOverlap($predictionA));
    }

    /**
     * @test
     */
    public function two_predictions_can_overlap_50_percent()
    {
        $predictionA = new AiPrediction();
        $predictionA->x_min = 0;
        $predictionA->x_max = 90;
        $predictionA->y_min = 0;
        $predictionA->y_max = 100;

        $predictionB = new AiPrediction();
        $predictionB->x_min = 30;
        $predictionB->x_max = 120;
        $predictionB->y_min = 0;
        $predictionB->y_max = 100;

        $this->assertEquals(0.5, $predictionA->percentageOverlap($predictionB));
        $this->assertEquals(0.5, $predictionB->percentageOverlap($predictionA));
    }

    /**
     * @test
     */
    public function a_completely_masked_prediction_is_masked()
    {
        $prediction = new AiPrediction([
            'object_class'=> 'person',
            'confidence' => '1.00',
            'x_min' => 295,
            'x_max' => 451,
            'y_min' => 95,
            'y_max' => 523
        ]);

        $path = Storage::disk('public_testing')->path('masks/test-mask3.png');

        $this->assertTrue($prediction->isMasked($path));
    }

    /**
     * @test
     */
    public function a_mostly_masked_prediction_is_masked()
    {
        $prediction = new AiPrediction([
            'object_class'=> 'person',
            'confidence' => '1.00',
            'x_min' => 295,
            'x_max' => 451,
            'y_min' => 95,
            'y_max' => 523
        ]);

        $path = Storage::disk('public_testing')->path('masks/test-mask2.png');

        $this->assertTrue($prediction->isMasked($path));
    }

    /**
     * @test
     */
    public function a_mostly_unmasked_prediction_is_not_masked()
    {
        $prediction = new AiPrediction([
            'object_class'=> 'person',
            'confidence' => '1.00',
            'x_min' => 295,
            'x_max' => 451,
            'y_min' => 95,
            'y_max' => 523
        ]);

        $path = Storage::disk('public_testing')->path('masks/test-mask1.png');

        $this->assertFalse($prediction->isMasked($path));
    }

    /**
     * @test
     */
    public function a_completely_unmasked_prediction_is_not_masked()
    {
        $prediction = new AiPrediction([
            'object_class'=> 'person',
            'confidence' => '1.00',
            'x_min' => 295,
            'x_max' => 451,
            'y_min' => 95,
            'y_max' => 523
        ]);

        $path = Storage::disk('public_testing')->path('masks/test-mask4.png');

        $this->assertFalse($prediction->isMasked($path));
    }
}
