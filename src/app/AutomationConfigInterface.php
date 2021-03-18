<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface AutomationConfigInterface
{
    public function detectionProfiles(): MorphToMany;

    public function getTable();

    public function delete();

    public function run(DetectionEvent $event, DetectionProfile $profile): bool;
}
