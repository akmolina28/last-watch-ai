<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\DeepstackCall.
 *
 * @property-read mixed $run_time_seconds
 * @method static Builder|DeepstackCall newModelQuery()
 * @method static Builder|DeepstackCall newQuery()
 * @method static Builder|DeepstackCall query()
 * @mixin Eloquent
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $input_file
 * @property Carbon|null $called_at
 * @property Carbon|null $returned_at
 * @property string|null $response_json
 * @property int $is_error
 * @property int $runTimeSeconds
 * @property array $predictions
 * @method static Builder|DeepstackCall whereCalledAt($value)
 * @method static Builder|DeepstackCall whereCreatedAt($value)
 * @method static Builder|DeepstackCall whereId($value)
 * @method static Builder|DeepstackCall whereInputFile($value)
 * @method static Builder|DeepstackCall whereIsError($value)
 * @method static Builder|DeepstackCall whereResponseJson($value)
 * @method static Builder|DeepstackCall whereReturnedAt($value)
 * @method static Builder|DeepstackCall whereUpdatedAt($value)
 * @property int $detection_event_id
 * @method static Builder|DeepstackCall whereDetectionEventId($value)
 * @property-read mixed $error
 * @property-read mixed $success
 */
class DeepstackCall extends Model
{
    protected $fillable = [
        'input_file',
        'called_at',
        'returned_at',
        'response_json',
        'is_error',
    ];

    protected $casts = [
        'is_error' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'called_at',
        'returned_at',
    ];

    public function getSuccessAttribute()
    {
        if ($this->response_json === null) {
            return false;
        }

        $response = json_decode($this->response_json);

        return $response->success;
    }

    public function getErrorAttribute()
    {
        if ($this->response_json === null) {
            return null;
        }

        $response = json_decode($this->response_json);

        return $response->error;
    }

    public function getRunTimeSecondsAttribute()
    {
        if ($this->called_at === null || $this->returned_at === null) {
            return -1;
        }

        return $this->returned_at->diffInSeconds($this->called_at);
    }

    public function getPredictionsAttribute()
    {
        if ($this->response_json === null) {
            return null;
        }

        $response = json_decode($this->response_json);

        return $response->predictions;
    }
}
