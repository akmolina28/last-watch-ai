<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * DetectionProfile.
 *
 * @mixin Eloquent
 * @property int $id
 * @property string $name
 * @property string $copy_to
 * @property int $overwrite
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|DetectionProfile[] $detectionProfiles
 * @property-read int|null $detection_profiles_count
 * @method static Builder|FolderCopyConfig newModelQuery()
 * @method static Builder|FolderCopyConfig newQuery()
 * @method static Builder|FolderCopyConfig query()
 * @method static Builder|FolderCopyConfig whereCopyTo($value)
 * @method static Builder|FolderCopyConfig whereCreatedAt($value)
 * @method static Builder|FolderCopyConfig whereId($value)
 * @method static Builder|FolderCopyConfig whereName($value)
 * @method static Builder|FolderCopyConfig whereOverwrite($value)
 * @method static Builder|FolderCopyConfig whereUpdatedAt($value)
 */
class FolderCopyConfig extends Model implements AutomationConfigInterface
{
    use SoftDeletes;

    protected $fillable = ['name', 'copy_to', 'overwrite'];

    public function detectionProfiles(): MorphToMany
    {
        return $this->morphToMany('App\DetectionProfile', 'automation_config');
    }

    public function run(DetectionEvent $event, DetectionProfile $profile): DetectionEventAutomationResult
    {
        $basename = basename($event->image_file_name);
        $ext = pathinfo($event->image_file_name, PATHINFO_EXTENSION);

        if ($this->overwrite) {
            $basename = $profile->slug.'.'.$ext;
        }

        $src = $event->image_file_name;
        $dest = $this->copy_to.$basename;

        $success = copy($src, $dest);

        return new DetectionEventAutomationResult([
            'response_text' => $success ? '' : 'Failed to copy '.$src.' to '.$dest,
            'is_error' => ! $success,
        ]);
    }

    protected static function booted()
    {
        static::deleted(function ($config) {
            $config->update(['name' => time().'::'.$config->name]);
        });
    }
}
