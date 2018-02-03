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

Route::post('client/request', 'ClientRequestController@create');

Route::middleware('auth:api')->group(function() {
    Route::get('/bots', 'BotController@index');
    Route::get('/bots/{bot}', 'BotController@show')->middleware('can:view,bot');
});