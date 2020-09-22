<?php

use App\DetectionEvent;
use App\DetectionProfile;
use App\FolderCopyConfig;
use App\Resources\DetectionEventResource;
use App\Resources\DetectionProfileResource;
use App\Resources\FolderCopyConfigResource;
use App\Resources\TelegramConfigResource;
use App\Resources\WebRequestConfigResource;
use App\TelegramConfig;
use App\WebRequestConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/profiles', function(Request $request) {
    return DetectionProfileResource::collection(DetectionProfile::paginate(10));
});

Route::post('/profiles', function(Request $request) {
    $request->validate([
        'name' => 'required',
        'file_pattern' => 'required',
        'min_confidence' => 'required',
        'object_classes' => 'required'
    ]);

    $profile = DetectionProfile::make([
        'name' => $request->get('name'),
        'file_pattern' => $request->get('file_pattern'),
        'min_confidence' => $request->get('min_confidence'),
        'use_regex' => $request->get('use_regex') == 'on',
        'object_classes' => $request->get('object_classes')
    ]);

    $file = $request->file('mask');
    if ($file) {
        $file->storeAs('masks', $profile->slug.'.png', 'public');
        $profile->use_mask = true;
    }
    else {
        $profile->use_mask = false;
    }

    $profile->save();

    return DetectionProfileResource::make($profile);
});

Route::get('/profiles/{profile}', function(DetectionProfile $profile) {
    return DetectionProfileResource::make($profile);
});

Route::post('/profiles/{profile}/subscriptions', function(DetectionProfile $profile) {
    Log::info('here');

    $type = request()->get('type');
    $id = request()->get('id');
    $value = request()->get('value');

    if ($type == 'telegram') {
        if ($value == 'true') {
            $profile->telegramConfigs()->sync([$id], false);
        }
        else {
            $profile->telegramConfigs()->detach($id);
        }
    }
    else if ($type == 'webRequest') {
        if ($value == 'true') {
            $profile->webRequestConfigs()->sync([$id], false);
        }
        else {
            $profile->webRequestConfigs()->detach($id);
        }
    }
    else if ($type == 'folderCopy') {
        if ($value == 'true') {
            $profile->folderCopyConfigs()->sync([$id], false);
        }
        else {
            $profile->folderCopyConfigs()->detach($id);
        }
    }
    else {
        throw new Exception('Invalid type '.$type);
    }

    return true;
});

Route::get('/events', function(Request $request) {
    return DetectionEventResource::collection(
        DetectionEvent::withCount(['detectionProfiles'])
            ->orderByDesc('occurred_at')
            ->paginate(10)
    );
});

Route::get('/objectClasses', function(Request $request) {
    return config('app.deepstack_object_classes');
});

Route::get('/events/{event}', function(DetectionEvent $event) {
    $event->load(['aiPredictions.detectionProfiles']);
    return DetectionEventResource::make($event);
});

Route::get('/telegram', function() {
    return TelegramConfigResource::collection(
        TelegramConfig::with(['detectionProfiles'])->orderByDesc('created_at')->get()
    );
});

Route::post('/telegram', function(Request $request) {
    $request->validate([
        'name' => 'required|unique:telegram_configs',
        'token' => 'required',
        'chat_id' => 'required',
    ]);

    $config = TelegramConfig::create([
        'name' => $request->get('name'),
        'token' => $request->get('token'),
        'chat_id' => $request->get('chat_id')
    ]);

    return TelegramConfigResource::make($config);
});

Route::get('/webRequest', function() {
    return WebRequestConfigResource::collection(
        WebRequestConfig::with(['detectionProfiles'])->orderByDesc('created_at')->get()
    );
});

Route::post('/webRequest', function(Request $request) {
    $request->validate([
        'name' => 'required|unique:web_request_configs',
        'url' => 'required',
    ]);

    $config = WebRequestConfig::create([
        'name' => $request->get('name'),
        'url' => $request->get('url'),
    ]);

    return WebRequestConfigResource::make($config);
});

Route::get('/folderCopy', function() {
    return FolderCopyConfigResource::collection(
        FolderCopyConfig::with(['detectionProfiles'])->orderByDesc('created_at')->get()
    );
});

Route::post('/folderCopy', function(Request $request) {
    $request->validate([
        'name' => 'required|unique:folder_copy_configs',
        'copy_to' => 'required'
    ]);

    $config = FolderCopyConfig::create([
        'name' => $request->get('name'),
        'copy_to' => $request->get('copy_to'),
        'overwrite' => $request->get('overwrite', false)
    ]);

    return FolderCopyConfigResource::make($config);
});
