<?php

namespace App;

use App\Exceptions\AutomationException;
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
 *
 * @property int $id
 * @property string $name
 * @property string $copy_to
 * @property int $overwrite
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|DetectionProfile[] $detectionProfiles
 * @property-read int|null $detection_profiles_count
 *
 * @method static Builder|FolderCopyConfig newModelQuery()
 * @method static Builder|FolderCopyConfig newQuery()
 * @method static Builder|FolderCopyConfig query()
 * @method static Builder|FolderCopyConfig whereCopyTo($value)
 * @method static Builder|FolderCopyConfig whereCreatedAt($value)
 * @method static Builder|FolderCopyConfig whereId($value)
 * @method static Builder|FolderCopyConfig whereName($value)
 * @method static Builder|FolderCopyConfig whereOverwrite($value)
 * @method static Builder|FolderCopyConfig whereUpdatedAt($value)
 *
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|FolderCopyConfig onlyTrashed()
 * @method static Builder|FolderCopyConfig whereDeletedAt($value)
 * @method static Builder|FolderCopyConfig withTrashed()
 * @method static Builder|FolderCopyConfig withoutTrashed()
 */
class FolderCopyConfig extends Model implements AutomationConfigInterface
{
    use SoftDeletes;

    protected $fillable = ['name', 'copy_to', 'overwrite'];

    public function detectionProfiles(): MorphToMany
    {
        return $this->morphToMany('App\DetectionProfile', 'automation_config')
            ->withPivot(['deleted_at'])
            ->whereNull('automation_configs.deleted_at');
    }

    /**
     * @param  DetectionEvent  $event
     * @param  DetectionProfile  $profile
     * @return bool
     *
     * @throws AutomationException
     */
    public function run(DetectionEvent $event, DetectionProfile $profile): bool
    {
        $basename = basename($event->imageFile->file_name);
        $ext = pathinfo($event->imageFile->file_name, PATHINFO_EXTENSION);

        if ($this->overwrite) {
            $basename = $profile->slug.'.'.$ext;
        }

        $src = $event->imageFile->path;
        $dest = $this->copy_to.$basename;

        $success = copy($src, $dest);

        if (!$success) {
            throw new AutomationException('Unable to copy image to '.$dest);
        }

        return true;
    }

    protected static function booted()
    {
        static::deleted(function ($config) {
            $config->update(['name' => time().'::'.$config->name]);
        });
    }
}
