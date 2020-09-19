<?php

use App\DetectionEvent;
use App\DetectionProfile;
use App\Resources\DetectionEventResource;
use App\Resources\DetectionProfileResource;
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
