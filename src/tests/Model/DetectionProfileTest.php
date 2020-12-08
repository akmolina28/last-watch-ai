<?php

namespace Tests\Model;

use App\AiPrediction;
use App\DetectionEvent;
use App\DetectionProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DetectionProfileTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */
    public function a_detection_profile_is_active_by_default()
    {
        DetectionProfile::create([
            'name' => $this->faker->word(),
            'file_pattern' => $this->faker->word(),
            'object_classes' => ['person'],
            'use_regex' => false,
        ]);

        $profile = DetectionProfile::first();

        $this->assertEquals(1, $profile->is_enabled);
    }

    /**
     * @test
     */
    public function a_detection_profile_has_enabled_status()
    {
        DetectionProfile::create([
            'name' => $this->faker->word(),
            'file_pattern' => $this->faker->word(),
            'object_classes' => ['person'],
            'use_regex' => false,
        ]);

        $profile = DetectionProfile::first();

        $this->assertEquals('enabled', $profile->status);
    }

    /**
     * @test
     */
    public function a_detection_profile_has_disabled_status()
    {
        $profile = DetectionProfile::create([
            'name' => $this->faker->word(),
            'file_pattern' => $this->faker->word(),
            'object_classes' => ['person'],
            'use_regex' => false,
        ]);

        $profile->is_enabled = false;

        $this->assertEquals('disabled', $profile->status);
    }

    /**
     * @test
     */
    public function a_detection_profile_can_match_without_regex()
    {
        $profile = new DetectionProfile([
            'file_pattern' => 'driveway_camera',
            'use_regex' => false,
        ]);

        $file_name = 'driveway_camera.20200825_180814020.jpg';

        $this->assertTrue($profile->patternMatch($file_name));
    }

    /**
     * @test
     */
    public function a_detection_profile_can_mismatch_without_regex()
    {
        $profile = new DetectionProfile([
            'file_pattern' => 'patio_camera',
            'use_regex' => false,
        ]);

        $file_name = 'driveway_camera.20200825_180814020.jpg';

        $this->assertFalse($profile->patternMatch($file_name));
    }

    /**
     * @test
     */
    public function a_detection_profile_can_match_with_regex()
    {
        $profile = new DetectionProfile([
            'file_pattern' => '/\bdriveway_camera\b.*.jpg/',
            'use_regex' => true,
        ]);

        $file_name = 'driveway_camera.20200825_180814020.jpg';

        $this->assertTrue($profile->patternMatch($file_name));
    }

    /**
     * @test
     */
    public function a_detection_profile_can_mismatch_with_regex()
    {
        $profile = new DetectionProfile([
            'file_pattern' => '/\bdriveway_camera\b.*.png   /',
            'use_regex' => true,
        ]);

        $file_name = 'driveway_camera.20200825_180814020.jpg';

        $this->assertFalse($profile->patternMatch($file_name));
    }

    /**
     * @test
     */
    public function a_detection_profile_can_be_soft_deleted()
    {
        $profile = factory(DetectionProfile::class)->create();

        $profile->delete();

        $profile->refresh();

        $this->assertTrue($profile->trashed());
        $this->assertNotNull($profile->deleted_at);
    }

    /**
     * @test
     */
    public function a_detection_profile_can_be_soft_deleted_then_created_again()
    {
        $profileName = 'test';

        $profile = factory(DetectionProfile::class)->create([
            'name' => $profileName,
        ]);

        $id1 = $profile->id;

        $profile->delete();

        $profile = factory(DetectionProfile::class)->create([
            'name' => $profileName,
        ]);

        $id2 = $profile->id;

        $this->assertNotEquals($id2, $id1);
    }

    /**
     * @test
     */
    public function a_detection_profile_can_be_scheduled()
    {
        $profile = factory(DetectionProfile::class)->create();

        $profile->start_time = '08:00';
        $profile->end_time = '16:00';
        $profile->is_scheduled = true;
        $profile->save();

        $profile->refresh();

        $this->assertEquals('as_scheduled', $profile->status);
    }

    /**
     * @test
     */
    public function a_detection_profile_is_active_when_scheduled_am_to_pm()
    {
        $profile = factory(DetectionProfile::class)->create();

        $profile->start_time = '08:00';
        $profile->end_time = '16:00';
        $profile->is_scheduled = true;
        $profile->save();

        $profile->refresh();

        $date1 = Carbon::create(2020, 1, 1, 8, 0, 0);
        $date2 = Carbon::create(2020, 1, 1, 9, 45, 7);
        $date3 = Carbon::create(2020, 1, 1, 12, 0, 33);
        $date4 = Carbon::create(2020, 1, 1, 15, 1, 21);
        $date5 = Carbon::create(2020, 1, 1, 15, 59, 59);

        $this->assertTrue($profile->isActive($date1));
        $this->assertTrue($profile->isActive($date2));
        $this->assertTrue($profile->isActive($date3));
        $this->assertTrue($profile->isActive($date4));
        $this->assertTrue($profile->isActive($date5));
    }

    /**
     * @test
     */
    public function a_detection_profile_is_inactive_when_not_scheduled_am_to_pm()
    {
        $profile = factory(DetectionProfile::class)->create();

        $profile->start_time = '08:00';
        $profile->end_time = '16:00';
        $profile->is_scheduled = true;
        $profile->save();

        $profile->refresh();

        $date1 = Carbon::create(2020, 1, 1, 16, 0, 0);
        $date2 = Carbon::create(2020, 1, 1, 17, 45, 7);
        $date3 = Carbon::create(2020, 1, 1, 23, 0, 33);
        $date4 = Carbon::create(2020, 1, 1, 0, 0, 0);
        $date5 = Carbon::create(2020, 1, 1, 7, 59, 59);

        $this->assertFalse($profile->isActive($date1));
        $this->assertFalse($profile->isActive($date2));
        $this->assertFalse($profile->isActive($date3));
        $this->assertFalse($profile->isActive($date4));
        $this->assertFalse($profile->isActive($date5));
    }

    /**
     * @test
     */
    public function a_detection_profile_is_active_when_scheduled_pm_to_am()
    {
        $profile = factory(DetectionProfile::class)->create();

        $profile->start_time = '21:30';
        $profile->end_time = '06:20';
        $profile->is_scheduled = true;
        $profile->save();

        $profile->refresh();

        $date1 = Carbon::create(2020, 1, 1, 21, 30, 0);
        $date2 = Carbon::create(2020, 1, 1, 23, 45, 7);
        $date3 = Carbon::create(2020, 1, 1, 1, 0, 33);
        $date4 = Carbon::create(2020, 1, 1, 4, 1, 21);
        $date5 = Carbon::create(2020, 1, 1, 6, 19, 59);

        $this->assertTrue($profile->isActive($date1));
        $this->assertTrue($profile->isActive($date2));
        $this->assertTrue($profile->isActive($date3));
        $this->assertTrue($profile->isActive($date4));
        $this->assertTrue($profile->isActive($date5));
    }

    /**
     * @test
     */
    public function a_detection_profile_is_inactive_when_not_scheduled_pm_to_am()
    {
        $profile = factory(DetectionProfile::class)->create();

        $profile->start_time = '21:30';
        $profile->end_time = '06:20';
        $profile->is_scheduled = true;
        $profile->save();

        $profile->refresh();

        $date1 = Carbon::create(2020, 1, 1, 6, 20, 0);
        $date2 = Carbon::create(2020, 1, 1, 8, 45, 7);
        $date3 = Carbon::create(2020, 1, 1, 12, 0, 33);
        $date4 = Carbon::create(2020, 1, 1, 16, 0, 0);
        $date5 = Carbon::create(2020, 1, 1, 21, 29, 59);

        $this->assertFalse($profile->isActive($date1));
        $this->assertFalse($profile->isActive($date2));
        $this->assertFalse($profile->isActive($date3));
        $this->assertFalse($profile->isActive($date4));
        $this->assertFalse($profile->isActive($date5));
    }

    protected function setUpSmartFilterData($predictionVars)
    {
        // create a profile
        $profile = factory(DetectionProfile::class)->create([
            'use_smart_filter' => true,
            'smart_filter_precision' => 0.95,
        ]);

        // create some events that contain the test prediction
        $events = factory(DetectionEvent::class, 3)->create()
            ->each(function ($event) use ($predictionVars) {
                $event->aiPredictions()->create($predictionVars);
            });

        // link each prediction to the profile
        foreach ($events as $event) {
            foreach ($event->aiPredictions as $prediction) {
                $profile->aiPredictions()->attach($prediction->id);
            }
        }

        return $profile;
    }

    /**
     * @test
     */
    public function a_profile_can_smart_filter_the_same_prediction()
    {
        // prediction to test
        $predictionVars = [
            'x_min' => 123,
            'x_max' => 404,
            'y_min' => 222,
            'y_max' => 669,
            'object_class' => 'person',
            'confidence' => 0.99,
        ];

        $profile = $this->setUpSmartFilterData($predictionVars);

        // create the test prediction
        $testPrediction = new AiPrediction($predictionVars);

        $event = DetectionEvent::latest()->first();

        $this->assertTrue($profile->isPredictionSmartFiltered($testPrediction, $event));
    }

    /**
     * @test
     */
    public function a_profile_can_smart_filter_a_similar_prediction()
    {
        // prediction to test
        $predictionVars = [
            'x_min' => 123,
            'x_max' => 404,
            'y_min' => 222,
            'y_max' => 669,
            'object_class' => 'person',
            'confidence' => 0.99,
        ];

        $profile = $this->setUpSmartFilterData($predictionVars);

        // create the test prediction
        $testPrediction = new AiPrediction([
            'x_min' => $predictionVars['x_min'] + 1,
            'x_max' => $predictionVars['x_max'] + 1,
            'y_min' => $predictionVars['y_min'] + 1,
            'y_max' => $predictionVars['y_max'] + 1,
            'object_class' => 'person',
            'confidence' => 0.99,
        ]);

        $event = DetectionEvent::latest()->first();

        $this->assertTrue($profile->isPredictionSmartFiltered($testPrediction, $event));
    }

    /**
     * @test
     */
    public function a_profile_can_not_smart_filter_a_non_similar_prediction()
    {
        // prediction to test
        $predictionVars = [
            'x_min' => 123,
            'x_max' => 404,
            'y_min' => 222,
            'y_max' => 669,
            'object_class' => 'person',
            'confidence' => 0.99,
        ];

        $profile = $this->setUpSmartFilterData($predictionVars);

        // create the test prediction
        $testPrediction = new AiPrediction([
            'x_min' => $predictionVars['x_min'] + 50,
            'x_max' => $predictionVars['x_max'] + 50,
            'y_min' => $predictionVars['y_min'] + 50,
            'y_max' => $predictionVars['y_max'] + 50,
            'object_class' => 'person',
            'confidence' => 0.99,
        ]);

        $event = DetectionEvent::latest()->first();

        $this->assertFalse($profile->isPredictionSmartFiltered($testPrediction, $event));
    }

    /**
     * @test
     */
    public function a_profile_can_smart_filter_a_non_precise_prediction()
    {
        // prediction to test
        $predictionVars = [
            'x_min' => 123,
            'x_max' => 404,
            'y_min' => 222,
            'y_max' => 669,
            'object_class' => 'person',
            'confidence' => 0.99,
        ];

        $profile = $this->setUpSmartFilterData($predictionVars);

        $profile->smart_filter_precision = 0.5;
        $profile->save();

        // create the test prediction
        $testPrediction = new AiPrediction([
            'x_min' => $predictionVars['x_min'] + 50,
            'x_max' => $predictionVars['x_max'] + 50,
            'y_min' => $predictionVars['y_min'] + 50,
            'y_max' => $predictionVars['y_max'] + 50,
            'object_class' => 'person',
            'confidence' => 0.99,
        ]);

        $event = DetectionEvent::latest()->first();

        $this->assertTrue($profile->isPredictionSmartFiltered($testPrediction, $event));
    }
}
