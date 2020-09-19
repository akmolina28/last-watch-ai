<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Eloquent;

/**
 * AiPrediction
 *
 * @mixin Eloquent
 */
class AiPrediction extends Model
{
    protected $fillable = ['object_class', 'confidence', 'x_min', 'x_max','y_min', 'y_max', 'detection_event_id',];

    public function detectionEvent()
    {
        return $this->belongsTo('App\DetectionEvent');
    }

    public function detectionProfiles()
    {
        return $this->belongsToMany('App\DetectionProfile');
    }
}
