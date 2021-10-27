<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\DetectionEventAutomationResult.
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $detection_event_id
 * @property int $automation_config_id
 * @property int $is_error
 * @property string|null $response_text
 *
 * @method static Builder|DetectionEventAutomationResult newModelQuery()
 * @method static Builder|DetectionEventAutomationResult newQuery()
 * @method static Builder|DetectionEventAutomationResult query()
 * @method static Builder|DetectionEventAutomationResult whereAutomationConfigId($value)
 * @method static Builder|DetectionEventAutomationResult whereCreatedAt($value)
 * @method static Builder|DetectionEventAutomationResult whereDetectionEventId($value)
 * @method static Builder|DetectionEventAutomationResult whereErrorText($value)
 * @method static Builder|DetectionEventAutomationResult whereId($value)
 * @method static Builder|DetectionEventAutomationResult whereIsError($value)
 * @method static Builder|DetectionEventAutomationResult whereResponseText($value)
 * @method static Builder|DetectionEventAutomationResult whereUpdatedAt($value)
 * @mixin Eloquent
 *
 * @property-read \App\AutomationConfig $automationConfig
 * @property-read \App\DetectionEvent $detectionEvent
 */
class DetectionEventAutomationResult extends Model
{
    protected $fillable = [
        'detection_event_id',
        'automation_config_id',
        'is_error',
        'response_text',
    ];

    public function automationConfig()
    {
        return $this->belongsTo('App\AutomationConfig');
    }

    public function detectionEvent()
    {
        return $this->belongsTo('App\DetectionEvent');
    }
}
