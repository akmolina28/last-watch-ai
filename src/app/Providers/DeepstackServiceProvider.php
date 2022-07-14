<?php

namespace App\Providers;

use App\DeepstackClient;
use App\DeepstackClientInterface;
use Illuminate\Support\ServiceProvider;

class DeepstackServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/deepstack.php' => config_path('deepstack.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/deepstack.php', 'deepstack');

        $this->app->bind(DeepstackClientInterface::class, function () {
            $config = config('deepstack');

            return new DeepstackClient($config['base_url']);
        });

        $this->app->alias(DeepstackClient::class, 'deepstack');
    }
}
