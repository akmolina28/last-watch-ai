<?php

use App\DetectionEvent;
use App\DetectionProfile;
use App\FolderCopyConfig;
use App\Resources\AutomationConfigResource;
use App\Resources\DetectionEventResource;
use App\Resources\DetectionProfileResource;
use App\Resources\FolderCopyConfigResource;
use App\Resources\SmbCifsCopyConfigResource;
use App\Resources\TelegramConfigResource;
use App\Resources\WebRequestConfigResource;
use App\SmbCifsCopyConfig;
use App\TelegramConfig;
use App\WebRequestConfig;
use Illuminate\Database\Eloquent\Relations\Relation;
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

Route::get('/profiles/{profile}/automations', function(DetectionProfile $profile) {
    $configTypes = [];

    $morphs = Relation::morphMap();
    foreach ($morphs as $alias => $type) {
        if (strpos($type, 'Config')) {
            array_push($configTypes, $alias);
        }
    }

    $union = false;
    $query = null;

    foreach ($configTypes as $type) {
        $q = Relation::$morphMap[$type]//::select('id', 'name', DB::raw("'".$type."' as type"));
            ::leftJoin('automation_configs as ac', function($join) use ($type, $profile) {
                $join->on('ac.automation_config_id', '=', $type.'.id');
                $join->where('ac.automation_config_type', '=', $type);
                $join->where('ac.detection_profile_id', '=', $profile->id);
            })
            ->select($type.'.id as id', DB::raw("'".$type."' as type"), 'ac.detection_profile_id as detection_profile_id', 'name');

        if ($union) {
            $query = $query->unionAll($q);
        }
        else {
            $query = $q;
            $union = true;
        }
    }

    return AutomationConfigResource::collection($query->get());
});

Route::post('/profiles/{profile}/automations', function(DetectionProfile $profile) {
    $type = request()->get('type');
    $id = request()->get('id');
    $value = request()->get('value');


    if ($value == 'true') {
        $count = DB::table('automation_configs')->where([
            ['detection_profile_id', '=', $profile->id],
            ['automation_config_id', '=', $id],
            ['automation_config_type', '=', $type],
        ])->count();

        if ($count == 0) {
            DB::table('automation_configs')->insert([
                'detection_profile_id' => $profile->id,
                'automation_config_id' => $id,
                'automation_config_type' => $type,
            ]);
        }
    }
    else {
        DB::table('automation_configs')->where([
            ['detection_profile_id', '=', $profile->id],
            ['automation_config_id', '=', $id],
            ['automation_config_type', '=', $type],
        ])->delete();
    }

    return true;
});

Route::get('/events', function(Request $request) {
    $query = DetectionEvent::query()
        ->withCount(['detectionProfiles' => function ($q) {
            $q->where('ai_prediction_detection_profile.is_masked', '=', false);
        }]);

    if ($request->has('profileId')) {

        $profileId = $request->get('profileId');

        $query->whereHas('detectionProfiles', function ($q) use ($profileId) {
            return $q->where('detection_profile_id', $profileId);
        });
    }
    else if ($request->has('relevant')) {

        $query->having('detection_profiles_count', '>', 0);
    }

    return DetectionEventResource::collection(
        $query
            ->orderByDesc('occurred_at')
            ->paginate(10)
    );
});

Route::get('/objectClasses', function(Request $request) {
    return config('deepstack.object_classes');
});

Route::get('/events/{event}', function(DetectionEvent $event) {
    $event->load(['aiPredictions.detectionProfiles', 'patternMatchedProfiles']);
    return DetectionEventResource::make($event);
});

Route::get('/automations/telegram', function() {
    return TelegramConfigResource::collection(
        TelegramConfig::with(['detectionProfiles'])->orderByDesc('created_at')->get()
    );
});

Route::post('/automations/telegram', function(Request $request) {
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

Route::get('/automations/webRequest', function() {
    return WebRequestConfigResource::collection(
        WebRequestConfig::with(['detectionProfiles'])->orderByDesc('created_at')->get()
    );
});

Route::post('/automations/webRequest', function(Request $request) {
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

Route::get('/automations/folderCopy', function() {
    return FolderCopyConfigResource::collection(
        FolderCopyConfig::with(['detectionProfiles'])->orderByDesc('created_at')->get()
    );
});

Route::post('/automations/folderCopy', function(Request $request) {
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

Route::get('/automations/smbCifsCopy', function() {
    return SmbCifsCopyConfigResource::collection(
        SmbCifsCopyConfig::with(['detectionProfiles'])->orderByDesc('created_at')->get()
    );
});

Route::post('/automations/smbCifsCopy', function(Request $request) {
    $request->validate([
        'name' => 'required|unique:folder_copy_configs',
        'servicename' => 'required',
        'user' => 'required',
        'password' => 'required',
        'remote_dest' => 'required'
    ]);

    $config = SmbCifsCopyConfig::create([
        'name' => $request->get('name'),
        'servicename' => $request->get('servicename'),
        'user' => $request->get('user'),
        'password' => $request->get('password'),
        'remote_dest' => $request->get('remote_dest'),
        'overwrite' => $request->get('overwrite', false)
    ]);

    return SmbCifsCopyConfigResource::make($config);
});
