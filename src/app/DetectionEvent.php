<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Eloquent;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * DetectionEvent
 *
 * @mixin Eloquent
 */
class DetectionEvent extends Model
{
    use HasRelationships;

    protected $fillable = ['image_file_name', 'deepstack_response', 'image_dimensions', 'occurred_at'];

    public function detectionProfiles()
    {
        return $this->hasManyDeep('App\DetectionProfile', ['App\AiPrediction', 'ai_prediction_detection_profile']);
    }

    public function patternMatchedProfiles()
    {
        return $this->belongsToMany('App\DetectionProfile', 'pattern_match');
    }

    public function aiPredictions()
    {
        return $this->hasMany('App\AiPrediction');
    }

    public function getDeepstackResultAttribute()
    {
        return json_decode($this->deepstack_response);
    }
}
