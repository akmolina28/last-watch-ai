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

Route::get('/profiles/{param}', 'DetectionProfileController@show');

Route::get('/profiles/{param}/edit', 'DetectionProfileController@edit');

Route::patch('/profiles/{param}', 'DetectionProfileController@update');

Route::delete('/profiles/{param}', 'DetectionProfileController@destroy');

Route::put('/profiles/{param}/status', 'DetectionProfileController@updateStatus');

Route::get('/profiles/{param}/automations', 'DetectionProfileController@showAutomations');

Route::put('/profiles/{param}/automations', 'DetectionProfileController@updateAutomations');

Route::get('/objectClasses', 'DeepstackController@showObjectClasses');

Route::get('/events', 'DetectionEventController@index');

Route::get('/events/latest', 'DetectionEventController@showLatest');

Route::get('/events/{event}', 'DetectionEventController@show');

Route::get('/events/{event}/prev', 'DetectionEventController@findPrev');

Route::get('/events/{event}/next', 'DetectionEventController@findNext');

Route::get('/events/{event}/img', 'DetectionEventController@showImage');

Route::get('/automations/telegram', 'AutomationController@telegramConfigIndex');

Route::post('/automations/telegram', 'AutomationController@makeTelegramConfig');

Route::delete('/automations/telegram/{config}', 'AutomationController@deleteTelegramConfig');

Route::get('/automations/webRequest', 'AutomationController@webRequestConfigIndex');

Route::post('/automations/webRequest', 'AutomationController@makeWebRequestConfig');

Route::delete('/automations/webRequest/{config}', 'AutomationController@deleteWebRequestConfig');

Route::get('/automations/folderCopy', 'AutomationController@folderCopyConfigIndex');

Route::post('/automations/folderCopy', 'AutomationController@makeFolderCopyConfig');

Route::delete('/automations/folderCopy/{config}', 'AutomationController@deleteFolderCopyConfig');

Route::get('/automations/smbCifsCopy', 'AutomationController@smbCifsCopyConfigIndex');

Route::post('/automations/smbCifsCopy', 'AutomationController@makeSmbCifsCopyConfig');

Route::delete('/automations/smbCifsCopy/{config}', 'AutomationController@deleteSmbCifsCopyConfig');

Route::get('/automations/mqttPublish', 'AutomationController@mqttPublishConfigIndex');

Route::post('/automations/mqttPublish', 'AutomationController@makeMqttPublishConfig');

Route::delete('/automations/mqttPublish/{config}', 'AutomationController@deleteMqttPublishConfig');

Route::get('/statistics', 'StatisticsController@index');

Route::get('/alive', 'StatisticsController@isAlive');

Route::get('/errors', 'StatisticsController@errors');

Route::get('/deepstackLogs', 'StatisticsController@deepstackLogs');

Route::any('/{any}', 'ErrorController@catchAll')->where('any', '.*');
