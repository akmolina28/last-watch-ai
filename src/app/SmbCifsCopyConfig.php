<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * DetectionProfile
 *
 * @mixin Eloquent
 */
class SmbCifsCopyConfig extends Model
{
    protected $fillable = ['name', 'servicename', 'user', 'password', 'remote_dest', 'overwrite'];

    public function detectionProfiles()
    {
        return $this->belongsToMany('App\DetectionProfile');
    }
}
