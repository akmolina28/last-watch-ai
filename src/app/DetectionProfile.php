<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
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
    use SoftDeletes;

    protected $fillable = ['name', 'file_pattern', 'min_confidence', 'use_regex', 'object_classes'];

    protected $casts = [
        'object_classes' => 'array'
    ];

    public function getStatusAttribute()
    {
        if ($this->is_enabled) {
            if ($this->is_scheduled) {
                return 'as_scheduled';
            }
            return 'enabled';
        }
        else {
            return 'disabled';
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

    public function timeToInt($time) {
        $hour = substr($time, 0, 2);
        $minute = substr($time, 3, 2);

        return $hour + ($minute / 60);
    }

    public function isActiveForDate(Carbon $date)
    {
        $start = $this->timeToInt($this->start_time);
        $end = $this->timeToInt($this->end_time);

        $now = $this->timeToInt($date->format('H:i'));

        if ($start < $end) {
            if ($now >= $start && $now < $end) {
                return true;
            }
        }
        else {
            if ($now >= $start || $now < $end) {
                return true;
            }
        }

        return false;
    }

    public function isActive(Carbon $date)
    {
        if ($this->is_enabled) {
            if ($this->is_scheduled) {
                return $this->isActiveForDate($date);
            }
            else {
                return true;
            }
        }
        return false;
    }
}
