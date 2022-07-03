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

Route::get('/profiles', 'DetectionProfileController@index')->middleware('auth.basic');

Route::post('/profiles', 'DetectionProfileController@make')->middleware('auth.basic');

Route::get('/profiles/{param}', 'DetectionProfileController@show')->middleware('auth.basic');

Route::get('/profiles/{param}/edit', 'DetectionProfileController@edit')->middleware('auth.basic');

Route::patch('/profiles/{param}', 'DetectionProfileController@update')->middleware('auth.basic');

Route::delete('/profiles/{param}', 'DetectionProfileController@destroy')->middleware('auth.basic');

Route::put('/profiles/{param}/status', 'DetectionProfileController@updateStatus')->middleware('auth.basic');

Route::get('/profiles/{param}/automations', 'DetectionProfileController@showAutomations')->middleware('auth.basic');

Route::put('/profiles/{param}/automations', 'DetectionProfileController@updateAutomations')->middleware('auth.basic');

Route::get('/objectClasses', 'DeepstackController@showObjectClasses')->middleware('auth.basic');

Route::get('/events', 'DetectionEventController@index')->middleware('auth.basic');

Route::post('/events', 'DetectionEventController@make');

Route::get('/events/latest', 'DetectionEventController@showLatest')->middleware('auth.basic');

Route::get('/events/viewer', 'DetectionEventController@viewer')->middleware('auth.basic');

Route::get('/events/{event}', 'DetectionEventController@show')->middleware('auth.basic');

Route::get('/events/{event}/prev', 'DetectionEventController@findPrev')->middleware('auth.basic');

Route::get('/events/{event}/next', 'DetectionEventController@findNext')->middleware('auth.basic');

Route::get('/events/{event}/img', 'DetectionEventController@showImage')->middleware('auth.basic');

Route::get('/events/{event}/alertImage', 'DetectionEventController@alertImage')->middleware('auth.basic');

Route::get('/automations/replacements', 'AutomationController@getReplacements')->middleware('auth.basic');

Route::get('/automations/telegram', 'AutomationController@telegramConfigIndex')->middleware('auth.basic');

Route::post('/automations/telegram', 'AutomationController@makeTelegramConfig')->middleware('auth.basic');

Route::delete('/automations/telegram/{config}', 'AutomationController@deleteTelegramConfig')->middleware('auth.basic');

Route::get('/automations/webRequest', 'AutomationController@webRequestConfigIndex')->middleware('auth.basic');

Route::post('/automations/webRequest', 'AutomationController@makeWebRequestConfig')->middleware('auth.basic');

Route::delete('/automations/webRequest/{config}', 'AutomationController@deleteWebRequestConfig')->middleware('auth.basic');

Route::get('/automations/folderCopy', 'AutomationController@folderCopyConfigIndex')->middleware('auth.basic');

Route::post('/automations/folderCopy', 'AutomationController@makeFolderCopyConfig')->middleware('auth.basic');

Route::delete('/automations/folderCopy/{config}', 'AutomationController@deleteFolderCopyConfig')->middleware('auth.basic');

Route::get('/automations/smbCifsCopy', 'AutomationController@smbCifsCopyConfigIndex')->middleware('auth.basic');

Route::post('/automations/smbCifsCopy', 'AutomationController@makeSmbCifsCopyConfig')->middleware('auth.basic');

Route::delete('/automations/smbCifsCopy/{config}', 'AutomationController@deleteSmbCifsCopyConfig')->middleware('auth.basic');

Route::get('/automations/mqttPublish', 'AutomationController@mqttPublishConfigIndex')->middleware('auth.basic');

Route::post('/automations/mqttPublish', 'AutomationController@makeMqttPublishConfig')->middleware('auth.basic');

Route::delete('/automations/mqttPublish/{config}', 'AutomationController@deleteMqttPublishConfig')->middleware('auth.basic');

Route::get('/statistics', 'StatisticsController@index')->middleware('auth.basic');

Route::get('/alive', 'StatisticsController@isAlive')->middleware('auth.basic');

Route::get('/errors', 'StatisticsController@errors')->middleware('auth.basic');

Route::get('/deepstackLogs', 'StatisticsController@deepstackLogs')->middleware('auth.basic');

Route::any('/{any}', 'ErrorController@catchAll')->where('any', '.*')->middleware('auth.basic');
