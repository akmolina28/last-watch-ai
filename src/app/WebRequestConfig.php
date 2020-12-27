<?php

namespace App;

use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * WebRequestConfig.
 *
 * @mixin Eloquent
 * @property int $id
 * @property string $name
 * @property string $url
 * @property bool $is_post
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
 * @method static Builder|WebRequestConfig whereBodyJson($value)
 * @method static Builder|WebRequestConfig whereHeadersJson($value)
 * @method static Builder|WebRequestConfig whereIsPost($value)
 */
class WebRequestConfig extends Model implements AutomationConfigInterface
{
    protected $fillable = ['name', 'url', 'is_post', 'body_json', 'headers_json'];

    protected $casts = [
        'is_post' => 'boolean',
    ];

    public function detectionProfiles(): MorphToMany
    {
        return $this->morphToMany('App\DetectionProfile', 'automation_config');
    }

    public function run(DetectionEvent $event, DetectionProfile $profile): DetectionEventAutomationResult
    {
        $headers = $this->getHeadersWithReplacements($event, $profile);
        $url = $this->getUrlWithReplacements($event, $profile);
        $body = $this->getBodyWithReplacements($event, $profile);

        if ($this->is_post) {
            return $this->postRequest($headers, $url, $body);
        } else {
            return $this->getRequest($headers, $url);
        }
    }

    public function getUrlWithReplacements(DetectionEvent $event, DetectionProfile $profile)
    {
        return PayloadHelper::doReplacements($this->url, $event, $profile);
    }

    public function getHeadersWithReplacements(DetectionEvent $event, DetectionProfile $profile)
    {
        if ($this->headers_json) {
            $replaced = PayloadHelper::doReplacements($this->headers_json, $event, $profile);

            return json_decode($replaced, true);
        }

        return [];
    }

    public function getBodyWithReplacements(DetectionEvent $event, DetectionProfile $profile)
    {
        if ($this->body_json) {
            $replaced = PayloadHelper::doReplacements($this->body_json, $event, $profile);

            return json_decode($replaced, true);
        }

        return [];
    }

    protected function postRequest(array $headers, string $url, array $body): DetectionEventAutomationResult
    {
        try {
            $response = Http::withHeaders($headers)->post($url, $body);
            $isError = ! in_array($response->status(), [200, 201]);
            $responseText = $response->body();
        } catch (Exception $exception) {
            $isError = true;
            $responseText = $exception->getMessage();
        }

        return new DetectionEventAutomationResult([
            'is_error' => $isError,
            'response_text' => $responseText,
        ]);
    }

    protected function getRequest(array $headers, string $url): DetectionEventAutomationResult
    {
        try {
            $response = Http::withHeaders($headers)->get($url);
            $isError = $response->status() != 200;
            $responseText = $response->body();
        } catch (Exception $exception) {
            $isError = true;
            $responseText = $exception->getMessage();
        }

        return new DetectionEventAutomationResult([
            'is_error' => $isError,
            'response_text' => $responseText,
        ]);
    }
}
