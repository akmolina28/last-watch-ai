<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * AiPrediction.
 *
 * @mixin Eloquent
 *
 * @property int $id
 * @property int $detection_event_id
 * @property string $object_class
 * @property string $confidence
 * @property int $x_min
 * @property int $x_max
 * @property int $y_min
 * @property int $y_max
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DetectionEvent $detectionEvent
 * @property-read Collection|DetectionProfile[] $detectionProfiles
 * @property-read int|null $detection_profiles_count
 *
 * @method static Builder|AiPrediction newModelQuery()
 * @method static Builder|AiPrediction newQuery()
 * @method static Builder|AiPrediction query()
 * @method static Builder|AiPrediction whereConfidence($value)
 * @method static Builder|AiPrediction whereCreatedAt($value)
 * @method static Builder|AiPrediction whereDetectionEventId($value)
 * @method static Builder|AiPrediction whereId($value)
 * @method static Builder|AiPrediction whereObjectClass($value)
 * @method static Builder|AiPrediction whereUpdatedAt($value)
 * @method static Builder|AiPrediction whereXMax($value)
 * @method static Builder|AiPrediction whereXMin($value)
 * @method static Builder|AiPrediction whereYMax($value)
 * @method static Builder|AiPrediction whereYMin($value)
 */
class AiPrediction extends Model
{
    protected $fillable = ['object_class', 'confidence', 'x_min', 'x_max', 'y_min', 'y_max', 'detection_event_id'];

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

    public function isMasked(string $pathToMaskPng)
    {
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

        [$width, $height] = getimagesize($pathToMaskPng);
        $im = imagecreatefrompng($pathToMaskPng);

        $outsideMaskCount = 0;
        $outsideMaskThreshold = 5;
        for ($i = 0; $i < 9; $i++) {
            $x = $detectionPoints[$i][0];
            $y = $detectionPoints[$i][1];

            $isOutsideMask = false;

            if ($x >= $width || $y > $height) {
                $isOutsideMask = true;
            } else {
                $rgba = imagecolorat($im, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;

                // 0 is opaque, 127 is transparent
                if ($alpha > 117) {
                    $isOutsideMask = true;
                }
            }

            if ($isOutsideMask) {
                $outsideMaskCount++;
                if ($outsideMaskCount >= $outsideMaskThreshold) {
                    return false;
                }
            }
        }

        return true;
    }

    public function toArray()
    {
        $attributes = $this->attributesToArray();
        $attributes = array_merge($attributes, $this->relationsToArray());

        // Detect if there is a pivot value and return that as the default value
        if (isset($attributes['pivot']['detection_profile_id'])) {
            $attributes['detection_profile_id'] = $attributes['pivot']['detection_profile_id'];
            $attributes['is_masked'] = $attributes['pivot']['is_masked'];
            $attributes['is_smart_filtered'] = $attributes['pivot']['is_smart_filtered'];
            unset($attributes['pivot']);
        }

        return $attributes;
    }
}
