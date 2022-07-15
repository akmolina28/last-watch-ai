<?php

namespace App;

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
 * @property-read Collection|\App\DeepstackCall[] $deepstackCalls
 * @property int|null $image_file_id
 * @property-read mixed $event_url
 * @property-read mixed $image_url
 * @property-read ImageFile|null $imageFile
 * @method static Builder|DetectionEvent whereImageFileId($value)
 * @property bool $is_processed
 * @property-read int|null $deepstack_calls_count
 * @property-read mixed $image_download
 * @property-read mixed $thumb_url
 * @method static Builder|DetectionEvent whereIsProcessed($value)
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
            ->withPivot('ai_prediction_detection_profile', [
                'is_relevant',
                'is_masked',
                'is_smart_filtered',
                'is_size_filtered',
                'is_confidence_filtered',
                'is_zone_ignored',
            ]);
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

    public function deepstackCalls()
    {
        return $this->hasMany('App\DeepstackCall');
    }

    public function imageFile()
    {
        return $this->belongsTo('App\ImageFile');
    }

    public function matchEventToProfiles(Collection $profiles)
    {
        $activeMatchedProfiles = [];

        foreach ($profiles as $profile) {
            $profile_active = $profile->isActive($this->occurred_at);
            $pattern_match = $profile->patternMatch($this->imageFile->file_name);

            if ($pattern_match) {
                if ($profile_active) {
                    array_push($activeMatchedProfiles, $profile);
                }

                $this->patternMatchedProfiles()->attach($profile->id, ['is_profile_active' => $profile_active]);
            }
        }

        return collect($activeMatchedProfiles);
    }

    public function getEventUrlAttribute()
    {
        if ($this->id) {
            return url('/events/' . $this->id);
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
            return url('/api/events/' . $this->id . '/img');
        }

        return null;
    }

    public function getNextEventId($relevantProfileId, $groupId, $ascending = true)
    {
        $query = DetectionEvent::where('occurred_at', $ascending ? '>=' : '<=', $this->occurred_at)
            ->where('id', '!=', $this->id);

        if ($ascending) {
            $query = $query->orderBy('occurred_at');
        } else {
            $query = $query->orderBy('occurred_at', 'desc');
        }

        $query = $query->whereHas('detectionProfiles', function ($q) use ($relevantProfileId, $groupId) {
            $q->where('ai_prediction_detection_profile.is_relevant', '=', true);
            if ($groupId) {
                $q->whereHas('profileGroups', function ($r) use ($groupId) {
                    return $r->where('profile_group_id', '=', $groupId);
                });
            } elseif ($relevantProfileId) {
                $q->where('detection_profile_id', '=', $relevantProfileId);
            }

            return $q;
        });

        $next = $query->first();

        return $next ? $next->id : null;
    }

    public function toArray()
    {
        $attributes = $this->attributesToArray();
        $attributes = array_merge($attributes, $this->relationsToArray());

        $attributes['image_url'] = $this->imageUrl;

        return $attributes;
    }
}
