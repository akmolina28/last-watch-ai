<?php

namespace App;

use App\Jobs\DeleteEventImageJob;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * DetectionEvent.
 *
 * @mixin Eloquent
 * @property int $id
 * @property string $image_file_name
 * @property string|null $deepstack_response
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $image_dimensions
 * @property Carbon|null $occurred_at
 * @property string|null $eventUrl
 * @property string|null $imageUrl
 * @property string|null $thumbUrl
 * @property string|null $imageDownload
 * @property-read Collection|AiPrediction[] $aiPredictions
 * @property-read int|null $ai_predictions_count
 * @property-read Collection|DetectionProfile[] $patternMatchedProfiles
 * @property-read int|null $pattern_matched_profiles_count
 * @method static Builder|DetectionEvent newModelQuery()
 * @method static Builder|DetectionEvent newQuery()
 * @method static Builder|DetectionEvent query()
 * @method static Builder|DetectionEvent whereCreatedAt($value)
 * @method static Builder|DetectionEvent whereDeepstackResponse($value)
 * @method static Builder|DetectionEvent whereId($value)
 * @method static Builder|DetectionEvent whereImageDimensions($value)
 * @method static Builder|DetectionEvent whereImageFileName($value)
 * @method static Builder|DetectionEvent whereOccurredAt($value)
 * @method static Builder|DetectionEvent whereUpdatedAt($value)
 * @property-read Collection|\App\DetectionEventAutomationResult[] $automations
 * @property-read int|null $automations_count
 * @property-read Collection|\App\DetectionEventAutomationResult[] $automationResults
 * @property-read int|null $automation_results_count
 * @property-read DeepstackCall|null $deepstackCall
 * @property int|null $image_file_id
 * @property-read mixed $event_url
 * @property-read mixed $image_url
 * @property-read ImageFile|null $imageFile
 * @method static Builder|DetectionEvent whereImageFileId($value)
 */
class DetectionEvent extends Model
{
    use HasRelationships;

    protected $fillable = [
        'deepstack_call_id',
        'occurred_at',
        'image_file_id',
        'is_processed',
    ];

    protected $casts = [
        'is_processed' => 'boolean',
    ];

    protected $with = ['imageFile'];

    public function detectionProfiles()
    {
        return $this->hasManyDeep('App\DetectionProfile', ['App\AiPrediction', 'ai_prediction_detection_profile'])
            ->withPivot('ai_prediction_detection_profile', ['is_masked', 'is_smart_filtered']);
    }

    public function patternMatchedProfiles()
    {
        return $this->belongsToMany('App\DetectionProfile', 'pattern_match')
            ->withPivot('is_profile_active');
    }

    public function aiPredictions()
    {
        return $this->hasMany('App\AiPrediction');
    }

    public function automationResults()
    {
        return $this->hasMany('App\DetectionEventAutomationResult');
    }

    public function deepstackCall()
    {
        return $this->hasOne('App\DeepstackCall');
    }

    public function imageFile()
    {
        return $this->belongsTo('App\ImageFile');
    }

    public function matchEventToProfiles(Collection $profiles)
    {
        $activeMatchedProfiles = 0;

        foreach ($profiles as $profile) {
            $profile_active = $profile->isActive($this->occurred_at);
            $pattern_match = $profile->patternMatch($this->imageFile->file_name);

            if ($pattern_match) {
                if ($profile_active) {
                    $activeMatchedProfiles++;
                }

                $this->patternMatchedProfiles()->attach($profile->id, ['is_profile_active' => $profile_active]);
            }
        }

        return $activeMatchedProfiles;
    }

    public function getEventUrlAttribute()
    {
        if ($this->id) {
            return url('/events/'.$this->id);
        }

        return null;
    }

    public function getImageUrlAttribute()
    {
        if ($this->imageFile) {
            return url($this->imageFile->getStoragePath());
        }

        return null;
    }

    public function getThumbUrlAttribute()
    {
        if ($this->imageFile) {
            return url($this->imageFile->getStoragePath(true));
        }

        return null;
    }

    public function getImageDownloadAttribute()
    {
        if ($this->id) {
            return url('/api/events/'.$this->id.'/img');
        }

        return null;
    }

    public function toArray()
    {
        $attributes = $this->attributesToArray();
        $attributes = array_merge($attributes, $this->relationsToArray());

        $attributes['image_url'] = $this->imageUrl;

        return $attributes;
    }

    protected static function booted()
    {
        static::deleted(function ($event) {
            if ($event->imageFile) {
                DeleteEventImageJob::dispatch($event->imageFile)->onQueue('low');
            }
        });
    }
}
