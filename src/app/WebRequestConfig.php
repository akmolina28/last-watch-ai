<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * DetectionProfile
 *
 * @mixin Eloquent
 */
class WebRequestConfig extends Model
{
    protected $fillable = ['name', 'url'];

    public function detectionProfiles()
    {
        return $this->belongsToMany('App\DetectionProfile', 'detection_profile_web_request_cfg');
    }
}
