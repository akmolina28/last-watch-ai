<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\DetectionProfile;

class DetectionProfileTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * @test
     */
    public function a_detection_profile_can_match_without_regex()
    {
        $profile = new DetectionProfile([
            'file_pattern' => 'driveway_camera',
            'use_regex' => false
        ]);

        $file_name = 'driveway_camera.20200825_180814020.jpg';

        $this->assertTrue($profile->pattern_match($file_name));
    }

    /**
     * @test
     */
    public function a_detection_profile_can_mismatch_without_regex()
    {
        $profile = new DetectionProfile([
            'file_pattern' => 'patio_camera',
            'use_regex' => false
        ]);

        $file_name = 'driveway_camera.20200825_180814020.jpg';

        $this->assertFalse($profile->pattern_match($file_name));
    }

    /**
     * @test
     */
    public function a_detection_profile_can_match_with_regex()
    {
        $profile = new DetectionProfile([
            'file_pattern' => '/\bdriveway_camera\b.*.jpg/',
            'use_regex' => true
        ]);

        $file_name = 'driveway_camera.20200825_180814020.jpg';

        $this->assertTrue($profile->pattern_match($file_name));
    }

    /**
     * @test
     */
    public function a_detection_profile_can_mismatch_with_regex()
    {
        $profile = new DetectionProfile([
            'file_pattern' => '/\bdriveway_camera\b.*.png   /',
            'use_regex' => true
        ]);

        $file_name = 'driveway_camera.20200825_180814020.jpg';

        $this->assertFalse($profile->pattern_match($file_name));
    }
}
