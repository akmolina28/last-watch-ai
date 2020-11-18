<?php

namespace App;

use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WebRequestConfig
 *
 * @mixin Eloquent
 * @property int $id
 * @property string $name
 * @property string $url
 * @property boolean $is_post
 * @property string $body_json
 * @property string $headers_json
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|\App\DetectionProfile[] $detectionProfiles
 * @property-read int|null $detection_profiles_count
 * @method static Builder|WebRequestConfig newModelQuery()
 * @method static Builder|WebRequestConfig newQuery()
 * @method static Builder|WebRequestConfig query()
 * @method static Builder|WebRequestConfig whereCreatedAt($value)
 * @method static Builder|WebRequestConfig whereId($value)
 * @method static Builder|WebRequestConfig whereName($value)
 * @method static Builder|WebRequestConfig whereUpdatedAt($value)
 * @method static Builder|WebRequestConfig whereUrl($value)
 */
class WebRequestConfig extends Model implements AutomationConfigInterface
{
    protected $fillable = ['name', 'url', 'is_post', 'body_json', 'headers_json'];

    protected $casts = [
        'is_post' => 'boolean',
    ];

    public function detectionProfiles()
    {
        return $this->morphToMany('App\DetectionProfile', 'automation_config');
    }

    public function run(DetectionEvent $event, DetectionProfile $profile) : DetectionEventAutomationResult
    {
        $headers = $this->headers_json ? json_decode($this->headers_json, true) : [];

        if ($this->is_post) {
            Log::info('post');

            return $this->postRequest($headers);
        }
        else {
            Log::info('get');
            return $this->getRequest($headers);
        }
    }

    protected function postRequest(Array $headers) : DetectionEventAutomationResult
    {
        $body = $this->body_json ? json_decode($this->body_json, true) : [];

        try {
            $response = Http::withHeaders($headers)->post($this->url, $body);
            $isError = !in_array($response->status(), [200, 201]);
            $responseText = $response->body();
        } catch (Exception $exception) {
            $isError = true;
            $responseText = $exception->getMessage();
        }

        return new DetectionEventAutomationResult([
            'is_error' => $isError,
            'response_text' => $responseText
        ]);
    }

    protected function getRequest(Array $headers) : DetectionEventAutomationResult
    {
        try {
            $response = Http::withHeaders($headers)->get($this->url);
            $isError = $response->status() != 200;
            $responseText = 'OK';
        } catch (Exception $exception) {
            $isError = true;
            $responseText = $exception->getMessage();
        }

        return new DetectionEventAutomationResult([
            'is_error' => $isError,
            'response_text' => $responseText
        ]);
    }
}
