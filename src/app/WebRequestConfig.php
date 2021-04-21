<?php

namespace App;

use App\Exceptions\AutomationException;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Client\Response;
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
 * @property Carbon|null $deleted_at
 * @method static Builder|WebRequestConfig onlyTrashed()
 * @method static Builder|WebRequestConfig whereDeletedAt($value)
 * @method static Builder|WebRequestConfig withTrashed()
 * @method static Builder|WebRequestConfig withoutTrashed()
 */
class WebRequestConfig extends Model implements AutomationConfigInterface
{
    use SoftDeletes;

    protected $fillable = ['name', 'url', 'is_post', 'body_json', 'headers_json'];

    protected $casts = [
        'is_post' => 'boolean',
    ];

    public function detectionProfiles(): MorphToMany
    {
        return $this->morphToMany('App\DetectionProfile', 'automation_config')
            ->withPivot(['deleted_at'])
            ->whereNull('automation_configs.deleted_at');
    }

    public function run(DetectionEvent $event, DetectionProfile $profile): bool
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

    /**
     * @param array $headers
     * @param string $url
     * @param array $body
     * @return bool
     * @throws AutomationException
     */
    protected function postRequest(array $headers, string $url, array $body): bool
    {
        $response = Http::withHeaders($headers)->post($url, $body);

        $this->checkResponseForErrors($response);

        return true;
    }

    /**
     * @param array $headers
     * @param string $url
     * @return bool
     * @throws AutomationException
     */
    protected function getRequest(array $headers, string $url): bool
    {
        $response = Http::withHeaders($headers)->get($url);

        $this->checkResponseForErrors($response);

        return true;
    }

    /**
     * @param Response $response
     * @throws AutomationException
     */
    protected function checkResponseForErrors(Response $response)
    {
        $isError = intval($response->status() / 200) !== 1; // check status is 2XX

        if ($isError) {
            $message = $response->status().' | '.$response->body();
            throw AutomationException::automationFailure($message);
        }
    }

    protected static function booted()
    {
        static::deleted(function ($config) {
            $config->update(['name' => time().'::'.$config->name]);
        });
    }
}
