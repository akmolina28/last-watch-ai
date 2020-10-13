<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * DetectionProfile
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
class SmbCifsCopyConfig extends Model
{
    protected $fillable = ['name', 'servicename', 'user', 'password', 'remote_dest', 'overwrite'];

    public function detectionProfiles()
    {
        return $this->morphToMany('App\DetectionProfile', 'automation_config');
    }
}
