<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\IgnoreZone
 *
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone query()
 * @mixin \Eloquent
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $detection_profile_id
 * @property string $object_class
 * @property int $x_min
 * @property int $x_max
 * @property int $y_min
 * @property int $y_max
 * @property string $expires_at
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone whereDetectionProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone whereExpireAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone whereObjectClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone whereXMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone whereXMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone whereYMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IgnoreZone whereYMin($value)
 * @property-read \App\DetectionProfile $detectionProfile
 */
class IgnoreZone extends Model
{
    protected $fillable = [
        'detection_profile_id',
        'object_class',
        'x_min',
        'x_max',
        'y_min',
        'y_max',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function detectionProfile()
    {
        return $this->belongsTo('App\DetectionProfile');
    }
}
