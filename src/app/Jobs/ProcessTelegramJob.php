<?php

namespace App\Jobs;

use App\DetectionEvent;
use App\TelegramClient;
use App\TelegramConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTelegramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $event;
    protected $config;

    /**
     * Create a new job instance.
     *
     * @param  DetectionEvent  $event
     * @return void
     */
    public function __construct(DetectionEvent $event, TelegramConfig $config)
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
        $client = new TelegramClient($this->config->token, $this->config->chat_id);

        $client->sendPhoto($this->event->image_file_name);
    }
}
