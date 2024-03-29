<?php

namespace App;

use App;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\AutomationConfig.
 *
 * @property int $id
 * @property int $detection_profile_id
 * @property string $automation_config_type
 * @property int $automation_config_id
 * @property bool @is_high_priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @method static Builder|AutomationConfig newModelQuery()
 * @method static Builder|AutomationConfig newQuery()
 * @method static Builder|AutomationConfig query()
 * @method static Builder|AutomationConfig whereAutomationConfigId($value)
 * @method static Builder|AutomationConfig whereAutomationConfigType($value)
 * @method static Builder|AutomationConfig whereCreatedAt($value)
 * @method static Builder|AutomationConfig whereDeletedAt($value)
 * @method static Builder|AutomationConfig whereDetectionProfileId($value)
 * @method static Builder|AutomationConfig whereId($value)
 * @method static Builder|AutomationConfig whereUpdatedAt($value)
 * @mixin Eloquent
 *
 * @method static Builder|AutomationConfig onlyTrashed()
 * @method static Builder|AutomationConfig withTrashed()
 * @method static Builder|AutomationConfig withoutTrashed()
 *
 * @property bool $is_high_priority
 *
 * @method static Builder|AutomationConfig whereIsHighPriority($value)
 */
class AutomationConfig extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'automation_config_id',
        'automation_config_type',
        'detection_profile_id',
        'is_high_priority',
    ];

    protected $casts = [
        'is_high_priority' => 'boolean',
    ];

    public function getConfigClassName()
    {
        return Relation::morphMap()[$this->automation_config_type];
    }

    public function run(DetectionEvent $event, DetectionProfile $profile): bool
    {
        $className = $this->getConfigClassName();

        /* @var $config AutomationConfigInterface */
        $config = App::make($className)->find($this->automation_config_id);

        return $config->run($event, $profile);
    }
}
