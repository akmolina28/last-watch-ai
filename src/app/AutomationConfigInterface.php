<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface AutomationConfigInterface
{
    public function detectionProfiles(): MorphToMany;

    public function run(DetectionEvent $event, DetectionProfile $profile): DetectionEventAutomationResult;
}
