<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * DetectionProfile.
 *
 * @mixin Eloquent
 * @property int $id
 * @property string $name
 * @property string $servicename
 * @property string $user
 * @property string $password
 * @property string $remote_dest
 * @property int $overwrite
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|DetectionProfile[] $detectionProfiles
 * @property-read int|null $detection_profiles_count
 * @method static Builder|SmbCifsCopyConfig newModelQuery()
 * @method static Builder|SmbCifsCopyConfig newQuery()
 * @method static Builder|SmbCifsCopyConfig query()
 * @method static Builder|SmbCifsCopyConfig whereCreatedAt($value)
 * @method static Builder|SmbCifsCopyConfig whereId($value)
 * @method static Builder|SmbCifsCopyConfig whereName($value)
 * @method static Builder|SmbCifsCopyConfig whereOverwrite($value)
 * @method static Builder|SmbCifsCopyConfig wherePassword($value)
 * @method static Builder|SmbCifsCopyConfig whereRemoteDest($value)
 * @method static Builder|SmbCifsCopyConfig whereServicename($value)
 * @method static Builder|SmbCifsCopyConfig whereUpdatedAt($value)
 * @method static Builder|SmbCifsCopyConfig whereUser($value)
 */
class SmbCifsCopyConfig extends Model implements AutomationConfigInterface
{
    use SoftDeletes;

    protected $fillable = ['name', 'servicename', 'user', 'password', 'remote_dest', 'overwrite'];

    public function detectionProfiles(): MorphToMany
    {
        return $this->morphToMany('App\DetectionProfile', 'automation_config');
    }

    public function run(DetectionEvent $event, DetectionProfile $profile): DetectionEventAutomationResult
    {
        $localPath = Storage::path($event->image_file_name);
        $destPath = $event->image_file_name;

        if ($this->overwrite) {
            $ext = pathinfo($event->image_file_name, PATHINFO_EXTENSION);
            $destPath = $profile->slug.'.'.$ext;
        }

        // todo: use format string
        // todo: use facade so this can be mocked
        $cmd = 'smbclient '
            .$this->servicename
            .' -U '.$this->user.'%'.$this->password
            .' -c \'cd "'.$this->remote_dest
            .'" ; put "'.$localPath.'" "'.$destPath.'"\'';

        $response = shell_exec($cmd);

        return new DetectionEventAutomationResult([
            'response_text' => $response,
            'is_error' => 0,
        ]);
    }

    protected static function booted()
    {
        static::deleted(function ($config) {
            $config->update(['name' => time().'::'.$config->name]);
        });
    }
}
