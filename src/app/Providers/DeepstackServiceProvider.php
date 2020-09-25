<?php

namespace App\Providers;

use App\DeepstackClient;
use Illuminate\Support\ServiceProvider;

class DeepstackServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/deepstack.php' => config_path('deepstack.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/deepstack.php', 'deepstack');

        $this->app->bind(DeepstackClient::class, function () {
            $config = config('deepstack');

//            $this->guardAgainstInvalidConfiguration($config);

            return new DeepstackClient($config['base_url']);
        });

        $this->app->alias(DeepstackClient::class, 'deepstack');
    }

//    protected function guardAgainstInvalidConfiguration(array $config = null)
//    {
//        if (empty($config['calendar_id'])) {
//            throw InvalidConfiguration::calendarIdNotSpecified();
//        }
//
//        $authProfile = $config['default_auth_profile'];
//
//        switch ($authProfile) {
//            case 'service_account':
//                $this->validateServiceAccountConfigSettings($config);
//                break;
//            case 'oauth':
//                $this->validateOAuthConfigSettings($config);
//                break;
//            default:
//                throw new \InvalidArgumentException("Unsupported authentication profile [{$authProfile}].");
//        }
//    }
//
//    protected function validateServiceAccountConfigSettings(array $config = null)
//    {
//        $credentials = $config['auth_profiles']['service_account']['credentials_json'];
//
//        $this->validateConfigSetting($credentials);
//    }
//
//    protected function validateOAuthConfigSettings(array $config = null)
//    {
//        $credentials = $config['auth_profiles']['oauth']['credentials_json'];
//
//        $this->validateConfigSetting($credentials);
//
//        $token = $config['auth_profiles']['oauth']['token_json'];
//
//        $this->validateConfigSetting($token);
//    }
//
//    protected function validateConfigSetting(string $setting)
//    {
//        if (! is_array($setting) && ! is_string($setting)) {
//            throw InvalidConfiguration::credentialsTypeWrong($setting);
//        }
//
//        if (is_string($setting) && ! file_exists($setting)) {
//            throw InvalidConfiguration::credentialsJsonDoesNotExist($setting);
//        }
//    }
}
