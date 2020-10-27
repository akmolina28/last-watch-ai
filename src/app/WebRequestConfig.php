<?php

namespace App;

use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * DetectionProfile
 *
 * @mixin Eloquent
 * @property int $id
 * @property string $name
 * @property string $url
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
    protected $fillable = ['name', 'url'];

    public function detectionProfiles()
    {
        return $this->morphToMany('App\DetectionProfile', 'automation_config');
    }

    public function run(DetectionEvent $event, DetectionProfile $profile) : DetectionEventAutomationResult
    {
        try {
            $response = Http::get($this->url);
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
