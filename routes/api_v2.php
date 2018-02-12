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

Route::post('host_requests', 'HostRequestController@create');
Route::get('host_requests/{host_request}', 'HostRequestController@show');
Route::post('host_requests/{host_request}/access', 'HostRequestController@access');

Route::middleware('auth:api')->group(function() {
    Route::get('/users/{user}', 'UserController@show')->middleware('can:view,user');

    Route::get('/bots', 'BotController@index');
    Route::get('/bots/{bot}', 'BotController@show')->middleware('can:view,bot');

    Route::post('hosts/refresh', 'TokenController@refresh');
});