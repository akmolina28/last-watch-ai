<?php

namespace Tests\Model;

use App\ProfileGroup;
use App\DetectionProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileGroupTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_create_group()
    {
        ProfileGroup::create(['name' => 'Test Group']);
        $group = ProfileGroup::first();
        $this->assertEquals('Test Group', $group->name);
    }

    /**
     * @test
     */
    public function cannot_create_duplicate_group()
    {
        ProfileGroup::create(['name' => 'Test Group']);
        $this->expectException(\Illuminate\Database\QueryException::class);
        ProfileGroup::create(['name' => 'Test Group']);
    }

    /**
     * @test
     */
    public function a_group_can_have_members()
    {
        $profiles = factory(DetectionProfile::class, 2)->create();
        $group = ProfileGroup::create(['name' => 'Test Group']);
        $group->detectionProfiles()->saveMany($profiles);
        $this->assertEquals(2, $group->detectionProfiles()->count());
    }

    /**
     * @test
     */
    public function a_profile_can_belong_to_groups()
    {
        $group_1 = ProfileGroup::create(['name' => 'Test Group 1']);
        $group_2 = ProfileGroup::create(['name' => 'Test Group 2']);
        $profile = factory(DetectionProfile::class)->create();
        $group_1->detectionProfiles()->save($profile);
        $group_2->detectionProfiles()->save($profile);
        $profile->refresh();
        $this->assertEquals(2, $profile->profileGroups()->count());
        $this->assertEquals(1, $profile->profileGroups()->where('name', '=', 'Test Group 1')->count());
        $this->assertEquals(1, $profile->profileGroups()->where('name', '=', 'Test Group 2')->count());
    }
}