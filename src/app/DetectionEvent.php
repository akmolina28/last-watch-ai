<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Eloquent;
use Illuminate\Support\Carbon;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * DetectionEvent
 *
 * @mixin Eloquent
 * @property int $id
 * @property string $image_file_name
 * @property string|null $deepstack_response
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $image_dimensions
 * @property Carbon|null $occurred_at
 * @property-read Collection|AiPrediction[] $aiPredictions
 * @property-read int|null $ai_predictions_count
 * @property-read mixed $deepstack_result
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
 */
class DetectionEvent extends Model
{
    use HasRelationships;

    protected $fillable = ['image_file_name', 'deepstack_response', 'image_dimensions', 'occurred_at'];

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

    public function getDeepstackResultAttribute()
    {
        return json_decode($this->deepstack_response);
    }

    public function matchEventToProfiles(Collection $profiles)
    {
        $activeMatchedProfiles = 0;

        foreach ($profiles as $profile) {
            $profile_active = $profile->isActive($this->occurred_at);
            $pattern_match = $profile->patternMatch($this->image_file_name);

            if ($pattern_match) {
                if ($profile_active) {
                    $activeMatchedProfiles++;
                }

                $this->patternMatchedProfiles()->attach($profile->id, ['is_profile_active' => $profile_active]);
            }
        }

        return $activeMatchedProfiles;
    }

//    public function saveAutomationResult($profileId, $)
}
