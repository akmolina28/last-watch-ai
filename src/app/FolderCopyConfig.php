<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * DetectionProfile
 *
 * @mixin Eloquent
 */
class FolderCopyConfig extends Model
{
    protected $fillable = ['name', 'copy_to', 'overwrite'];

    public function detectionProfiles()
    {
        return $this->belongsToMany('App\DetectionProfile');
    }
}
