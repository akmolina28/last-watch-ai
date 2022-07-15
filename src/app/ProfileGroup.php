<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * App\ProfileGroup
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $v_deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\DetectionProfile[] $detectionProfiles
 * @property-read int|null $detection_profiles_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGroup newQuery()
 * @method static \Illuminate\Database\Query\Builder|ProfileGroup onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGroup whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGroup whereVDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|ProfileGroup withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ProfileGroup withoutTrashed()
 * @mixin \Eloquent
 */
class ProfileGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public static function findByUnique($unique)
    {
        return ProfileGroup::where('id', $unique)
            ->orWhere('slug', $unique)
            ->firstOrFail();
    }

    public function detectionProfiles()
    {
        return $this->belongsToMany('App\DetectionProfile');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value, '-');
    }
}
