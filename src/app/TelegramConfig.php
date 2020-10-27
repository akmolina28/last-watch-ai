<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * DetectionProfile
 *
 * @mixin Eloquent
 * @property int $id
 * @property string $name
 * @property string $token
 * @property string $chat_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|DetectionProfile[] $detectionProfiles
 * @property-read int|null $detection_profiles_count
 * @method static Builder|TelegramConfig newModelQuery()
 * @method static Builder|TelegramConfig newQuery()
 * @method static Builder|TelegramConfig query()
 * @method static Builder|TelegramConfig whereChatId($value)
 * @method static Builder|TelegramConfig whereCreatedAt($value)
 * @method static Builder|TelegramConfig whereId($value)
 * @method static Builder|TelegramConfig whereName($value)
 * @method static Builder|TelegramConfig whereToken($value)
 * @method static Builder|TelegramConfig whereUpdatedAt($value)
 */
class TelegramConfig extends Model implements AutomationConfigInterface
{
    protected $fillable = ['name', 'token', 'chat_id'];

    public function detectionProfiles()
    {
        return $this->morphToMany('App\DetectionProfile', 'automation_config');
    }

    public function run(DetectionEvent $event, DetectionProfile $profile): DetectionEventAutomationResult
    {
        $client = new TelegramClient($this->token, $this->chat_id);

        $response = $client->sendPhoto(Storage::path($event->image_file_name));

        $responseJson = json_decode($response);

        return new DetectionEventAutomationResult([
            'response_text' => $response,
            'is_error' => !$responseJson->ok,
        ]);
    }
}
