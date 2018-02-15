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

Route::prefix('host_requests')
    ->group(function () {
        Route::post('/', 'HostRequestController@create');
        Route::get('{host_request}', 'HostRequestController@show');
        Route::post('{host_request}/access', 'HostRequestController@access');
    });

Route::middleware('auth:api')
    ->group(function () {
        Route::get('users/{user}', 'UserController@show')
            ->middleware('can:view,user')
            ->middleware('scope:users');

        Route::get('bots', 'BotController@index')
            ->middleware('scope:bots');
        Route::get('bots/{bot}', 'BotController@show')
            ->middleware('can:view,bot')
            ->middleware('scope:bots');
    });

Route::prefix('hosts')
    ->middleware('scope:host')
    ->middleware('auth:api')
    ->group(function () {
        Route::post('refresh', 'TokenController@refresh');
    });