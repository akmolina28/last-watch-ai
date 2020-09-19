<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/{any}', 'SpaController@index')->where('any', '.*');

//Route::get('/', function () {
//    echo phpinfo();
//});
//
//Route::get("/profiles", 'DetectionProfileController@index');
//Route::get("/profiles/create", 'DetectionProfileController@create');
//Route::post("/profiles", 'DetectionProfileController@store');
//
//Route::get("/events", 'DetectionEventController@index');
//Route::get("/events/{event}", 'DetectionEventController@show');
//
Route::webhooks('/webhook-receiving-url');
