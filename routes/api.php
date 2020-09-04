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

Route::middleware('auth:api')
    ->group(function () {
        Route::get('users/{user}', 'UserController@show')
            ->middleware('can:view,user')
            ->middleware('scope:users');

        Route::prefix('bots')
            ->middleware('scope:bots')
            ->group(function () {
                Route::get('/', 'BotController@index');

                Route::get('{bot}', 'BotController@show')
                    ->middleware('can:view,bot');
            });
    });

Route::post('host', 'App\Http\Controllers\HostApiController@command')
    ->middleware('host');

Route::post('/broadcasting/auth', 'App\Http\Controllers\BroadcastController@auth')
    ->middleware('resolve_host');