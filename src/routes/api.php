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

Route::get('/profiles', 'DetectionProfileController@index');

Route::post('/profiles', 'DetectionProfileController@make');

Route::get('/profiles/{profile}', 'DetectionProfileController@show');

Route::delete('/profiles/{profile}', 'DetectionProfileController@destroy');

Route::put('/profiles/{profile}/status', 'DetectionProfileController@updateStatus');

Route::get('/profiles/{profile}/automations', 'DetectionProfileController@showAutomations');

Route::post('/profiles/{profile}/automations', 'DetectionProfileController@updateAutomations');

Route::get('/events', 'DetectionEventController@index');

Route::get('/objectClasses', 'DeepstackController@showObjectClasses');

Route::get('/events/{event}', 'DetectionEventController@show');

Route::get('/automations/telegram', 'AutomationController@telegramConfigIndex');

Route::post('/automations/telegram', 'AutomationController@makeTelegramConfig');

Route::get('/automations/webRequest', 'AutomationController@webRequestConfigIndex');

Route::post('/automations/webRequest', 'AutomationController@makeWebRequestConfig');

Route::get('/automations/folderCopy', 'AutomationController@folderCopyConfigIndex');

Route::post('/automations/folderCopy', 'AutomationController@makeFolderCopyConfig');

Route::get('/automations/smbCifsCopy', 'AutomationController@smbCifsCopyConfigIndex');

Route::post('/automations/smbCifsCopy', 'AutomationController@makeSmbCifsCopyConfig');

Route::any("/{any}", 'ErrorController@catchAll')->where('any', '.*');
