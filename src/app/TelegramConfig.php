<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * DetectionProfile
 *
 * @mixin Eloquent
 */
class TelegramConfig extends Model
{
    protected $fillable = ['name', 'token', 'chat_id'];

    public function detectionProfiles()
    {
        return $this->morphToMany('App\DetectionProfile', 'automation_config');
    }
}
