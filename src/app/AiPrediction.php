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
        return $this->belongsToMany('App\DetectionProfile')
            ->withPivot(['is_masked', 'is_smart_filtered']);
    }

    public function area()
    {
        $x_len = ($this->x_max ?? 0) - ($this->x_min ?? 0);
        $y_len = ($this->y_max ?? 0) - ($this->y_min ?? 0);

        return $x_len * $y_len;
    }

    public function percentageOverlap(AiPrediction $prediction)
    {
        $intersectingArea =
            max(0, min($this->x_max, $prediction->x_max) - max($this->x_min, $prediction->x_min))
          * max(0, min($this->y_max, $prediction->y_max) - max($this->y_min, $prediction->y_min));

        $unionArea = $this->area() + $prediction->area() - $intersectingArea;

        $ratio = $intersectingArea / $unionArea;

        return round($ratio, 4);
    }
}
