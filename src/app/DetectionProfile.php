<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * DetectionProfile.
 *
 * @mixin Eloquent
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $slug
 * @property string $file_pattern
 * @property array $object_classes
 * @property string $min_confidence
 * @property int $use_regex
 * @property int $use_mask
 * @property int $is_enabled
 * @property int $is_negative
 * @property Carbon|null $deleted_at
 * @property int $is_scheduled
 * @property string|null $start_time
 * @property string|null $end_time
 * @property int $use_smart_filter
 * @property string $smart_filter_precision
 * @property-read Collection|AiPrediction[] $aiPredictions
 * @property-read int|null $ai_predictions_count
 * @property-read mixed $status
 * @property-read Collection|DetectionEvent[] $patternMatchedEvents
 * @property-read int|null $pattern_matched_events_count
 *
 * @method static Builder|DetectionProfile newModelQuery()
 * @method static Builder|DetectionProfile newQuery()
 * @method static Builder|DetectionProfile onlyTrashed()
 * @method static Builder|DetectionProfile query()
 * @method static Builder|DetectionProfile whereCreatedAt($value)
 * @method static Builder|DetectionProfile whereDeletedAt($value)
 * @method static Builder|DetectionProfile whereEndTime($value)
 * @method static Builder|DetectionProfile whereFilePattern($value)
 * @method static Builder|DetectionProfile whereId($value)
 * @method static Builder|DetectionProfile whereIsEnabled($value)
 * @method static Builder|DetectionProfile whereIsScheduled($value)
 * @method static Builder|DetectionProfile whereMinConfidence($value)
 * @method static Builder|DetectionProfile whereName($value)
 * @method static Builder|DetectionProfile whereObjectClasses($value)
 * @method static Builder|DetectionProfile whereSlug($value)
 * @method static Builder|DetectionProfile whereSmartFilterPrecision($value)
 * @method static Builder|DetectionProfile whereStartTime($value)
 * @method static Builder|DetectionProfile whereUpdatedAt($value)
 * @method static Builder|DetectionProfile whereUseMask($value)
 * @method static Builder|DetectionProfile whereUseRegex($value)
 * @method static Builder|DetectionProfile whereUseSmartFilter($value)
 * @method static Builder|DetectionProfile withTrashed()
 * @method static Builder|DetectionProfile withoutTrashed()
 *
 * @property-read Collection|AutomationConfig[] $automations
 * @property-read int|null $automations_count
 *
 * @method static Builder|DetectionProfile whereIsNegative($value)
 */
class DetectionProfile extends Model
{
    use HasRelationships;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'file_pattern',
        'min_confidence',
        'use_regex',
        'object_classes',
        'use_smart_filter',
        'smart_filter_precision',
        'is_negative',
    ];

    protected $casts = [
        'object_classes' => 'array',
        'use_regex' => 'boolean',
        'use_smart_filter' => 'boolean',
        'is_negative' => 'boolean',
    ];

    public function getStatusAttribute()
    {
        if ($this->is_enabled) {
            if ($this->is_scheduled) {
                return 'as_scheduled';
            }

            return 'enabled';
        } else {
            return 'disabled';
        }
    }

    public function patternMatchedEvents()
    {
        return $this->belongsToMany('App\DetectionEvent', 'pattern_match');
    }

    public function detectionEvents()
    {
        return $this->hasManyDeep(
            DetectionEvent::class,
            ['ai_prediction_detection_profile', AiPrediction::class],
            [null, null, 'id'],
            [null, 'ai_prediction_id', 'detection_event_id']
        )
            ->withPivot('ai_prediction_detection_profile', ['is_masked', 'is_smart_filtered']);
    }

    public function aiPredictions()
    {
        return $this->belongsToMany('App\AiPrediction')
            ->withPivot(['is_masked', 'is_smart_filtered']);
    }

    public function automations()
    {
        return $this->hasMany('App\AutomationConfig');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value, '-');
    }

    public function patternMatch($file_name)
    {
        if ($this->use_regex) {
            return preg_match($this->file_pattern, $file_name) == 1;
        } else {
            return strpos($file_name, $this->file_pattern) !== false;
        }
    }

    public function timeToInt($time)
    {
        $hour = substr($time, 0, 2);
        $minute = substr($time, 3, 2);

        return $hour + ($minute / 60);
    }

    public function isActiveForDate(Carbon $date)
    {
        $start = $this->timeToInt($this->start_time);
        $end = $this->timeToInt($this->end_time);

        $now = $this->timeToInt($date->format('H:i'));

        if ($start < $end) {
            if ($now >= $start && $now < $end) {
                return true;
            }
        } else {
            if ($now >= $start || $now < $end) {
                return true;
            }
        }

        return false;
    }

    public function isActive(Carbon $date)
    {
        if ($this->is_enabled) {
            if ($this->is_scheduled) {
                return $this->isActiveForDate($date);
            } else {
                return true;
            }
        }

        return false;
    }

    public function subscribeAutomation($automationClass, $automationId, $isHighPriority = false)
    {
        $this->morphedByMany($automationClass, 'automation_config')
            ->withTimestamps()
            ->withPivot('is_high_priority', 'deleted_at')
            ->syncWithoutDetaching([
                $automationId => [
                    'is_high_priority' => $isHighPriority,
                    'deleted_at' => null,
                ],
            ]);
    }

    public function unsubscribeAutomation($automationClass, $automationId)
    {
        $type = array_search($automationClass, Relation::morphMap());

        AutomationConfig::where([
            ['detection_profile_id', '=', $this->id],
            ['automation_config_id', '=', $automationId],
            ['automation_config_type', '=', $type],
        ])->delete();
    }

    public function isPredictionSmartFiltered(AiPrediction $prediction, DetectionEvent $lastDetectionEvent)
    {
        $precision = $this->smart_filter_precision ?? 0.80;

        if (! $this->use_smart_filter) {
            return false;
        }

        if ($lastDetectionEvent == null) {
            return false;
        }

        $id = $this->id;

        // get predictions with the same object class
        $filterCandidates = $lastDetectionEvent
            ->aiPredictions()
            ->where('object_class', '=', $prediction->object_class)
            ->whereHas('detectionProfiles', function ($q) use ($id) {
                return $q
                    ->where('detection_profile_id', '=', $id)
                    ->where('ai_prediction_detection_profile.is_masked', '=', false);
            })->get();

        // see if any of the predictions overlap with the new prediction
        foreach ($filterCandidates as $candidate) {
            if ($candidate->percentageOverlap($prediction) >= $precision) {
                return true;
            }
        }

        return false;
    }
}
