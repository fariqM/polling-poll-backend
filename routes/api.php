<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::namespace('App\Http\Controllers\Api')->group(function () {
    Route::get('testing', 'TestingController@store');
    Route::prefix('/p')->group(function () {
        Route::post('create', 'PollingController@store');
        Route::get('{dir}', 'PollingController@show');
        Route::put('{polling:dir}/update', 'PollingController@update');
        Route::get('{dir}/{deviceId}', 'VotersController@checkDevice');
        Route::post('verify-password/{dir}', 'VotersController@checkPassword');
    });
    Route::prefix('/polling')->group(function () {
        Route::post('submit/{answer:id}', 'PollingController@submitPoll');
    });
    Route::get('my-poll/{deviceID}', 'PollingController@index');
});
