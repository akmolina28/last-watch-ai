<?php

namespace App\Jobs;

use App\DetectionEvent;
use App\WebRequestConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ProcessWebRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $event;
    protected $config;

    /**
     * Create a new job instance.
     *
     * @param  DetectionEvent  $event
     * @param  WebRequestConfig  $config
     * @return void
     */
    public function __construct(DetectionEvent $event, WebRequestConfig $config)
    {
        $this->event = $event;
        $this->config = $config;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() //todo: resolve client from container
    {
        $response = Http::get($this->config->url);
    }
}
