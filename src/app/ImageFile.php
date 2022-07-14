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
 *
 * @property int $id
 * @property string $file_name
 * @property string $path
 * @property int $width
 * @property int $height
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
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
        'privacy_mode',
    ];

    protected $casts = [
        'privacy_mode' => 'boolean',
    ];

    public static function findOrCreate($attributes = []): ImageFile
    {
        if ($attributes['path']) {
            $existingImage = ImageFile::where('path', $attributes['path']);

            if ($existingImage->count() > 0) {
                return $existingImage->first();
            }
        }

        return ImageFile::create($attributes);
    }

    public function getStoredExtension()
    {
        if ($this->path) {
            return pathinfo($this->path, PATHINFO_EXTENSION);
        }

        return null;
    }

    public function getStoredFilename()
    {
        if ($this->path) {
            return pathinfo($this->path, PATHINFO_FILENAME);
        }

        return null;
    }

    public function getStoredDirectoryName()
    {
        if ($this->path) {
            return pathinfo($this->path, PATHINFO_DIRNAME);
        }
    }

    public function getStoragePath($thumbnail = false)
    {
        if ($this->path) {
            return '/storage/' . $this->getPath($thumbnail);
        }

        return null;
    }

    public function getPath($thumbnail = false)
    {
        $path = $this->path;

        if ($thumbnail) {
            $path = $this->getStoredDirectoryName() . '/'
                . $this->getStoredFilename() . '-thumb.' . $this->getStoredExtension();
        }

        return $path;
    }

    public function getAbsolutePath($thumbnail = false)
    {
        return Storage::path($this->getPath($thumbnail));
    }
}
