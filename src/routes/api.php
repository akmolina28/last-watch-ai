<?php

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
