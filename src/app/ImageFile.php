<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * AiPrediction.
 *
 * @mixin Eloquent
 * @property int $id
 * @property string $file_name
 * @property string $path
 * @property int $width
 * @property int $height
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ImageFile newModelQuery()
 * @method static Builder|ImageFile newQuery()
 * @method static Builder|ImageFile query()
 * @method static Builder|ImageFile whereCreatedAt($value)
 * @method static Builder|ImageFile whereFileName($value)
 * @method static Builder|ImageFile whereHeight($value)
 * @method static Builder|ImageFile whereId($value)
 * @method static Builder|ImageFile wherePath($value)
 * @method static Builder|ImageFile whereUpdatedAt($value)
 * @method static Builder|ImageFile whereWidth($value)
 */
class ImageFile extends Model
{
    protected $fillable = [
        'file_name',
        'path',
        'width',
        'height',
    ];

    public function getAbsolutePath()
    {
        return Storage::path($this->path);
    }
}
