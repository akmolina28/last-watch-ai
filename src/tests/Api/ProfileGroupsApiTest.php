<?php

namespace Tests\Api;

use App\AiPrediction;
use App\AutomationConfig;
use App\DeepstackCall;
use App\DetectionEvent;
use App\DetectionProfile;
use App\FolderCopyConfig;
use App\ImageFile;
use App\Jobs\EnableDetectionProfileJob;
use App\MqttPublishConfig;
use App\ProfileGroup;
use App\SmbCifsCopyConfig;
use App\TelegramConfig;
use App\User;
use App\WebRequestConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileGroupsApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(
            ThrottleRequests::class
        );

        $user = new User(['name' => 'Administrator']);
        $this->be($user);
    }

    /**
     * @test
     */
    public function api_can_get_profile_groups()
    {
        factory(ProfileGroup::class, 3)->create();
        $this->json('GET', '/api/profileGroups')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'name',
                    'slug',
                    'detection_profiles',
                ]],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_profile_groups_with_profiles()
    {
        $group = factory(ProfileGroup::class)->create();
        $profiles = factory(DetectionProfile::class, 5)->create();
        $group->detectionProfiles()->saveMany($profiles);

        $this->json('GET', '/api/profileGroups')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data.0.detection_profiles')
            ->assertJsonStructure([
                'data' => [0 => [
                    'detection_profiles' => [0 => [
                        'id',
                        'name',
                        'slug',
                        'file_pattern',
                    ]],
                ]],
            ]);
    }

    /**
     * @test
     */
    public function api_can_create_a_profile_group()
    {
        $this->json('POST', '/api/profileGroups', [
            'name' => 'My Profile Group',
        ])
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_attach_groups_to_a_profile()
    {
        $profile = factory(DetectionProfile::class)->create();
        $groups = factory(ProfileGroup::class, 3)->create();
        $otherGroups = factory(ProfileGroup::class, 5)->create();

        $this->json('PUT', '/api/profiles/' . $profile->id . '/groups', [
            'group_ids' => [
                $groups[0]->id,
                $groups[1]->id,
                $groups[2]->id,
            ],
        ])->assertStatus(200);

        $profile->refresh();
        $this->assertEquals(3, $profile->profileGroups()->count());
        $this->assertEquals(1, $profile->profileGroups()->where('profile_groups.id', '=', $groups[0]->id)->count());
        $this->assertEquals(1, $profile->profileGroups()->where('profile_groups.id', '=', $groups[1]->id)->count());
        $this->assertEquals(1, $profile->profileGroups()->where('profile_groups.id', '=', $groups[2]->id)->count());
    }

    /**
     * @test
     */
    public function api_can_deattach_groups_from_a_profile()
    {
        $profile = factory(DetectionProfile::class)->create();
        $groups = factory(ProfileGroup::class, 3)->create();
        $otherGroups = factory(ProfileGroup::class, 5)->create();
        $profile->profileGroups()->saveMany($groups);

        $this->json('PUT', '/api/profiles/' . $profile->id . '/groups', [
            'group_ids' => [
                $groups[0]->id,
                $groups[2]->id,
            ],
        ])->assertStatus(200);

        $profile->refresh();
        $this->assertEquals(2, $profile->profileGroups()->count());
        $this->assertEquals(1, $profile->profileGroups()->where('profile_groups.id', '=', $groups[0]->id)->count());
        $this->assertEquals(0, $profile->profileGroups()->where('profile_groups.id', '=', $groups[1]->id)->count());
        $this->assertEquals(1, $profile->profileGroups()->where('profile_groups.id', '=', $groups[2]->id)->count());
    }

    /**
     * @test
     */
    public function api_can_deattach_all_groups_from_a_profile()
    {
        $profile = factory(DetectionProfile::class)->create();
        $groups = factory(ProfileGroup::class, 3)->create();
        $otherGroups = factory(ProfileGroup::class, 5)->create();
        $profile->profileGroups()->saveMany($groups);

        $this->json('PUT', '/api/profiles/' . $profile->id . '/groups', [
            'group_ids' => [],
        ])->assertStatus(200);

        $profile->refresh();
        $this->assertEquals(0, $profile->profileGroups()->count());
    }

    /**
     * @test
     */
    public function api_can_attach_a_profile_to_a_group()
    {
        $profile = factory(DetectionProfile::class)->create();
        $group = factory(ProfileGroup::class)->create();

        $this->assertFalse($group->detectionProfiles()->where('detection_profiles.id', '=', $profile->id)->exists());

        $this->json('PUT', '/api/profileGroups/' . $group->id . '/attachProfile?profileId=' . $profile->id)
            ->assertStatus(200);

        $this->assertTrue($group->detectionProfiles()->where('detection_profiles.id', '=', $profile->id)->exists());
    }

    /**
     * @test
     */
    public function api_can_detach_a_profile_from_a_group()
    {
        $profile = factory(DetectionProfile::class)->create();
        $group = factory(ProfileGroup::class)->create();
        $group->detectionProfiles()->save($profile);

        $this->assertTrue($group->detectionProfiles()->where('detection_profiles.id', '=', $profile->id)->exists());

        $this->json('PUT', '/api/profileGroups/' . $group->id . '/attachProfile', [
            'profileId' => $profile->id,
            'detach' => true,
        ])
            ->assertStatus(200);

        $this->assertFalse($group->detectionProfiles()->where('detection_profiles.id', '=', $profile->id)->exists());
    }

    /**
     * @test
     */
    public function api_can_destroy_a_profile_group()
    {
        $profile = factory(DetectionProfile::class)->create();
        $group = factory(ProfileGroup::class)->create();
        $group->detectionProfiles()->save($profile);

        $this->json('DELETE', '/api/profileGroups/' . $group->id)
            ->assertStatus(200);

        $group->refresh();
        $this->assertTrue($group->trashed());
    }
}
