<?php

namespace App;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProfileGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

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
