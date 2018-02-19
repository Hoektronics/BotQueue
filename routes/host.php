<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HOST Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('requests', 'HostRequestController@create');
Route::get('requests/{host_request}', 'HostRequestController@show');
Route::post('requests/{host_request}/access', 'HostRequestController@access');

Route::middleware('scope:host')
    ->middleware('auth:api')
    ->middleware('is_host')
    ->group(function () {
        Route::post('refresh', 'TokenController@refresh');

        Route::get('bots', 'HostController@bots');
    });
