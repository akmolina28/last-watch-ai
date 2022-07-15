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

class ProfilesApiTest extends TestCase
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
    public function api_can_get_all_profiles()
    {
        factory(DetectionProfile::class, 10)->create();

        $response = $this->get('/api/profiles');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [0 => [
                    'id',
                    'name',
                    'slug',
                    'file_pattern',
                    'object_classes',
                    'min_confidence',
                    'use_mask',
                    'use_regex',
                    'is_negative',
                    'use_smart_filter',
                    'smart_filter_precision',
                    'min_object_size',
                    'start_time',
                    'end_time',
                    'status',
                ]],
            ])
            ->assertJsonCount(10, 'data');
    }

    /**
     * @test
     */
    public function api_can_create_a_profile_without_a_mask()
    {
        $this->json('POST', '/api/profiles', [
            'name' => 'My Awesome Profile',
            'file_pattern' => 'camera123',
            'use_regex' => false,
            'object_classes' => '["car", "person"]',
            'min_confidence' => 0.42,
        ])
            ->assertStatus(201)
            ->assertJsonCount(1)
            ->assertJson([
                'data' => [
                    'name' => 'My Awesome Profile',
                    'slug' => 'my-awesome-profile',
                    'file_pattern' => 'camera123',
                    'use_mask' => false,
                    'object_classes' => [
                        0 => 'car',
                        1 => 'person',
                    ],
                    'min_confidence' => 0.42,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_create_a_profile_with_smart_filtering()
    {
        $this->json('POST', '/api/profiles', [
            'name' => 'My Awesome Profile',
            'file_pattern' => 'camera123',
            'use_regex' => 'false',
            'object_classes' => '["car", "person"]',
            'min_confidence' => 0.42,
            'use_smart_filter' => 'true',
            'smart_filter_precision' => 0.69,
        ])
            ->assertStatus(201)
            ->assertJsonCount(1)
            ->assertJson([
                'data' => [
                    'name' => 'My Awesome Profile',
                    'slug' => 'my-awesome-profile',
                    'file_pattern' => 'camera123',
                    'use_mask' => false,
                    'object_classes' => [
                        0 => 'car',
                        1 => 'person',
                    ],
                    'min_confidence' => 0.42,
                    'use_smart_filter' => true,
                    'smart_filter_precision' => 0.69,
                ],
            ]);

        $profile = DetectionProfile::first();

        $this->assertEquals(1, $profile->use_smart_filter);
        $this->assertEquals(0.69, $profile->smart_filter_precision);
    }

    /**
     * @test
     */
    public function api_can_create_a_negative_profile()
    {
        $this->json('POST', '/api/profiles', [
            'name' => 'My Awesome Profile',
            'file_pattern' => 'camera123',
            'use_regex' => 'false',
            'object_classes' => '["car", "person"]',
            'min_confidence' => 0.42,
            'use_smart_filter' => 'false',
            'is_negative' => 'true',
        ])
            ->assertStatus(201)
            ->assertJsonCount(1)
            ->assertJson([
                'data' => [
                    'name' => 'My Awesome Profile',
                    'is_negative' => true,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_delete_a_profile()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->json('DELETE', '/api/profiles/' . $profile->id)
            ->assertStatus(200);

        $profile->refresh();

        $this->assertTrue($profile->trashed());
    }

    /**
     * @test
     */
    public function api_can_reuse_name_of_deleted_profile()
    {
        $profileName = $this->faker->word();

        $profile = factory(DetectionProfile::class)->create([
            'name' => $profileName,
        ]);

        $profile->delete();

        $this->json('POST', '/api/profiles', [
            'name' => $profileName,
            'file_pattern' => 'camera123',
            'use_regex' => false,
            'object_classes' => '["car", "person"]',
            'min_confidence' => 0.42,
        ])
            ->assertStatus(201);
    }

    /**
     * @test
     */
    public function api_can_get_a_profile()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->get('/api/profiles/' . $profile->id)
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJson([
                'data' => [
                    'name' => $profile->name,
                    'slug' => $profile->slug,
                    'file_pattern' => $profile->file_pattern,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_active_profile_status()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->get('/api/profiles/' . $profile->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'enabled',
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_inactive_profile_status()
    {
        $profile = factory(DetectionProfile::class)->create();
        $profile->is_enabled = false;
        $profile->save();

        $this->get('/api/profiles/' . $profile->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'disabled',
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_scheduled_profile_status()
    {
        $profile = factory(DetectionProfile::class)->create();
        $profile->is_scheduled = true;
        $profile->save();

        $this->get('/api/profiles/' . $profile->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'as_scheduled',
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_scheduled_profile_disabled_status()
    {
        $profile = factory(DetectionProfile::class)->create();
        $profile->is_scheduled = true;
        $profile->is_enabled = false;
        $profile->save();

        $this->get('/api/profiles/' . $profile->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'disabled',
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_set_profile_status_inactive()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->json('PUT', '/api/profiles/' . $profile->id . '/status', [
            'status' => 'disabled',
        ])
            ->assertStatus(204);

        $profile->refresh();

        $this->assertEquals('disabled', $profile->status);
    }

    /**
     * @test
     */
    public function api_can_set_profile_status_inactive_for_period()
    {
        Queue::fake();

        $profile = factory(DetectionProfile::class)->create();

        $this->json('PUT', '/api/profiles/' . $profile->id . '/status', [
            'status' => 'disabled',
            'period' => 5,
        ])
            ->assertStatus(204);

        $profile->refresh();

        $this->assertEquals('disabled', $profile->status);

        Queue::assertPushed(
            EnableDetectionProfileJob::class,
            function ($job) use ($profile) {
                return $job->profile->id = $profile->id;
            }
        );
    }

    /**
     * @test
     */
    public function api_can_set_profile_status_active()
    {
        $profile = factory(DetectionProfile::class)->create();
        $profile->is_enabled = false;
        $profile->save();

        $this->json('PUT', '/api/profiles/' . $profile->id . '/status', [
            'status' => 'enabled',
        ])
            ->assertStatus(204);

        $profile->refresh();

        $this->assertEquals('enabled', $profile->status);
    }

    /**
     * @test
     */
    public function api_can_set_profile_status_as_scheduled()
    {
        $profile = factory(DetectionProfile::class)->create();
        $profile->is_scheduled = true;
        $profile->save();

        $this->json('PUT', '/api/profiles/' . $profile->id . '/status', [
            'status' => 'as_scheduled',
            'start_time' => '23:45',
            'end_time' => '12:34',
        ])
            ->assertStatus(204);

        $profile->refresh();

        $this->assertEquals('as_scheduled', $profile->status);
        $this->assertEquals('23:45', $profile->start_time);
        $this->assertEquals('12:34', $profile->end_time);
    }

    /**
     * @test
     */
    public function api_can_get_profile_for_editing()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->json('GET', '/api/profiles/' . $profile->id . '/edit')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $profile->id,
                    'name' => $profile->name,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_get_profile_by_slug_name()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->json('GET', '/api/profiles/' . $profile->slug . '/edit')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $profile->id,
                    'name' => $profile->name,
                ],
            ]);
    }

    /**
     * @test
     */
    public function api_can_update_a_profile()
    {
        $profile = factory(DetectionProfile::class)->create([
            'name' => 'testing123',
            'file_pattern' => 'testing123',
            'use_regex' => false,
            'min_confidence' => 0.55,
            'object_classes' => ['person', 'car'],
            'use_smart_filter' => false,
            'smart_filter_precision' => 0.42,
            'min_object_size' => null,
        ]);

        $this->json('PATCH', '/api/profiles/' . $profile->id, [
            'id' => $profile->id,
            'name' => 'testing123',
            'file_pattern' => '/\btesting456\b/',
            'use_regex' => 'true',
            'object_classes' => '["dog", "cat"]',
            'min_confidence' => 0.69,
            'use_smart_filter' => 'true',
            'smart_filter_precision' => 0.77,
            'min_object_size' => 1234,
        ])
            ->assertJson([
                'data' => [
                    'name' => 'testing123',
                ],
            ])
            ->assertStatus(200);

        $profile->refresh();

        $this->assertEquals('testing123', $profile->name);
        $this->assertEquals('/\btesting456\b/', $profile->file_pattern);
        $this->assertTrue($profile->use_regex);
        $this->assertContains('dog', $profile->object_classes);
        $this->assertContains('cat', $profile->object_classes);
        $this->assertCount(2, $profile->object_classes);
        $this->assertTrue($profile->use_smart_filter);
        $this->assertEquals(0.69, $profile->min_confidence);
        $this->assertEquals(0.77, $profile->smart_filter_precision);
        $this->assertEquals(1234, $profile->min_object_size);
    }

    /**
     * @test
     */
    public function api_throws_422_if_profile_status_update_invalid()
    {
        $profile = factory(DetectionProfile::class)->create();

        $this->json('PUT', '/api/profiles/' . $profile->id . '/status', [
            'status' => 'asdf',
        ])
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422);
    }

    /**
     * @test
     */
    public function api_throws_404_if_profile_status_update_invalid_id()
    {
        $this->json('PUT', '/api/profiles/999999/status', [
            'status' => 'asdf',
        ])
            ->assertStatus(404)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson([
                'message' => 'Not Found.',
            ]);
    }

    /**
     * @test
     */
    public function api_can_add_ignore_zone_to_profile()
    {

        /**
         * @var DetectionProfile
         */
        $profile = factory(DetectionProfile::class)->create();
        $profile->refresh();

        /**
         * @var DetectionEvent
         */
        $event = factory(DetectionEvent::class)->create();

        /**
         * @var AiPrediction
         */
        $prediction = factory(AiPrediction::class)->create([
            'detection_event_id' => $event->id,
        ]);

        $this->json('PUT', '/api/profiles/' . $profile->slug . '/ignoreZone', [
            'ai_prediction_id' => $prediction->id
        ])->assertStatus(200);

        $this->assertEquals(1, $profile->ignoreZones()->count());
        $this->assertEquals($prediction->x_min, $profile->ignoreZones()->first()->x_min);
        $this->assertEquals($prediction->x_max, $profile->ignoreZones()->first()->x_max);
        $this->assertEquals($prediction->y_min, $profile->ignoreZones()->first()->y_min);
        $this->assertEquals($prediction->y_max, $profile->ignoreZones()->first()->y_max);
        $this->assertEquals($prediction->object_class, $profile->ignoreZones()->first()->object_class);
        $this->assertNull($profile->ignoreZones()->first()->expires_at);
    }

    /**
     * @test
     */
    public function api_can_add_ignore_zone_to_profile_with_expiry()
    {

        /**
         * @var DetectionProfile
         */
        $profile = factory(DetectionProfile::class)->create();
        $profile->refresh();

        /**
         * @var DetectionEvent
         */
        $event = factory(DetectionEvent::class)->create();

        /**
         * @var AiPrediction
         */
        $prediction = factory(AiPrediction::class)->create([
            'detection_event_id' => $event->id,
        ]);

        $this->json('PUT', '/api/profiles/' . $profile->slug . '/ignoreZone', [
            'ai_prediction_id' => $prediction->id,
            'expiration_days' => 7
        ])->assertStatus(200);

        $this->assertEquals(1, $profile->ignoreZones()->count());
        $this->assertEquals($prediction->x_min, $profile->ignoreZones()->first()->x_min);
        $this->assertEquals($prediction->x_max, $profile->ignoreZones()->first()->x_max);
        $this->assertEquals($prediction->y_min, $profile->ignoreZones()->first()->y_min);
        $this->assertEquals($prediction->y_max, $profile->ignoreZones()->first()->y_max);
        $this->assertEquals($prediction->object_class, $profile->ignoreZones()->first()->object_class);
        $this->assertEquals(
            now()->addDays(7)->format('Y-m-d h:m:s'),
            $profile->ignoreZones()->first()->expires_at->format('Y-m-d h:m:s')
        );
    }
}
