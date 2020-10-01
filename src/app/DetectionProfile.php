<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Eloquent;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * DetectionProfile
 *
 * @mixin Eloquent
 */
class DetectionProfile extends Model
{
    use HasRelationships;

    protected $fillable = ['name', 'file_pattern', 'min_confidence', 'use_regex', 'object_classes'];

    protected $casts = [
        'object_classes' => 'array'
    ];

    public function getStatusAttribute()
    {
        if ($this->is_active) {
            return 'active';
        }
        else {
            return 'inactive';
        }
    }

    public function patternMatchedEvents()
    {
        return $this->belongsToMany('App\DetectionEvent', 'pattern_match');
    }

    public function detectionEvents()
    {
        return $this->hasManyDeep(
            DetectionEvent::class,
            ['ai_prediction_detection_profile', AiPrediction::class],
            [null, null, 'id'],
            [null, 'ai_prediction_id', 'detection_event_id']
        );
    }

    public function aiPredictions()
    {
        return $this->belongsToMany('App\AiPrediction')->withPivot('is_masked');
    }

    public function telegramConfigs()
    {
        return $this->morphedByMany('App\TelegramConfig', 'automation_config');
    }

    public function webRequestConfigs()
    {
        return $this->morphedByMany('App\WebRequestConfig', 'automation_config');
    }

    public function folderCopyConfigs()
    {
        return $this->morphedByMany('App\FolderCopyConfig', 'automation_config');
    }

    public function smbCifsCopyConfigs()
    {
        return $this->morphedByMany('App\SmbCifsCopyConfig', 'automation_config');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value, '-');
    }

    public function pattern_match($file_name)
    {
        if ($this->use_regex) {
            return preg_match($this->file_pattern, $file_name) == 1;
        }
        else {
            return strpos($file_name, $this->file_pattern) !== false;
        }
    }
}
