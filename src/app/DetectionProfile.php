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

    public function getRouteKeyName()
    {
        return "slug";
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
        return $this->belongsToMany('App\AiPrediction');
    }

    public function telegramConfigs()
    {
        return $this->belongsToMany('App\TelegramConfig');
    }

    public function folderCopyConfigs()
    {
        return $this->belongsToMany('App\FolderCopyConfig');
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
