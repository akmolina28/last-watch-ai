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

    public function isMasked($pathToMaskPng) {
        $detectionPoints = [
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
            [-1, -1],
        ];

        $k = 0;

        for ($i = 1; $i <= 3; $i++) {
            for ($j = 1; $j <= 3; $j++) {
                $x = $this->x_min + ($this->x_max - $this->x_min) * $i * 0.25;
                $y = $this->y_min + ($this->y_max - $this->y_min) * $j * 0.25;

                $detectionPoints[$k] = [$x, $y];
                $k++;
            }
        }

        $im = imagecreatefrompng($pathToMaskPng);
        $outsideMaskCount = 0;
        $outsideMaskThreshold = 5;
        for ($i = 0; $i < 9; $i++) {
            $x = $detectionPoints[$i][0];
            $y = $detectionPoints[$i][1];
            $rgba = imagecolorat($im,$x,$y);
            $alpha = ($rgba & 0x7F000000) >> 24;

            // 0 is opaque, 127 is transparent
            if ($alpha > 117) {
                $outsideMaskCount++;
                if ($outsideMaskCount >= $outsideMaskThreshold) {
                    return false;
                }
            }
        }

        return true;
    }
}
