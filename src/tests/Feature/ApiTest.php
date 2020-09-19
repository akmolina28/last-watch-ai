<?php

namespace Tests\Feature;

use App\AiPrediction;
use App\DetectionEvent;
use App\DetectionMask;
use App\DetectionProfile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

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
                'data' =>
                    [0 => [
                        'id',
                        'name',
                        'object_classes',
                        'min_confidence',
                        'use_mask'
                    ]]
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
            'object_classes' => ['car', 'person'],
            'min_confidence' => 0.42
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
                        1 =>'person'
                    ],
                    'min_confidence' => 0.42
                ]
            ]);
    }

    /**
     * @test
     */
    public function api_can_first_page_of_events()
    {
        factory(DetectionEvent::class, 25)
            ->create()
            ->each(function ($event) {
                $event->aiPredictions()->createMany(
                    factory(AiPrediction::class, 3)->make()->toArray()
                );
            });

        $response = $this->get('/api/events');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' =>
                    [0 => [
                        'id',
                        'image_file_name',
                        'detection_profiles_count'
                    ]]
            ])
            ->assertJsonCount(10, 'data');
    }

    /**
     * @test
     */
    public function api_can_get_event_details()
    {
        factory(DetectionEvent::class, 25)
            ->create()
            ->each(function ($event) {
                $event->aiPredictions()->createMany(
                    factory(AiPrediction::class, 3)->make()->toArray()
                );
            });
    }
}
