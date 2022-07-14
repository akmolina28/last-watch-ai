<?php

namespace App\Jobs;

use App\AutomationConfig;
use App\DetectionEvent;
use App\DetectionEventAutomationResult;
use App\DetectionProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessAutomationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public DetectionProfile $profile;
    public DetectionEvent $event;
    public AutomationConfig $automation;

    /**
     * Create a new job instance.
     *
     * @param  DetectionProfile  $profile
     * @param  DetectionEvent  $event
     * @param  AutomationConfig  $automation
     */
    public function __construct(DetectionProfile $profile, DetectionEvent $event, AutomationConfig $automation)
    {
        $this->profile = $profile;
        $this->event = $event;
        $this->automation = $automation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $success = $this->automation->run($this->event, $this->profile);

        DetectionEventAutomationResult::create([
            'detection_event_id' => $this->event->id,
            'automation_config_id' => $this->automation->id,
            'is_error' => !$success,
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param  Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        DetectionEventAutomationResult::create([
            'detection_event_id' => $this->event->id,
            'automation_config_id' => $this->automation->id,
            'is_error' => 1,
            'response_text' => $exception->getMessage(),
        ]);
    }
}
