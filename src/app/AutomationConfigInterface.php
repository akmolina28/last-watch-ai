<?php


namespace App;

interface AutomationConfigInterface
{
    public function run(DetectionEvent $event, DetectionProfile $profile) : DetectionEventAutomationResult;
}
