<?php

namespace Tests\Feature;

use App\AiPrediction;
use App\DetectionEvent;
use App\DetectionProfile;
use App\Exceptions\AutomationException;
use App\ImageFile;
use App\WebRequestConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MqttPublishAutomationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    // todo: add tests
}
