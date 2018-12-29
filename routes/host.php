<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HOST Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Host routes for BotQueue. These routes
| have the host middleware group applied, which gives authentication
| and host resolving functionality. This file is for host use only.
|
*/

Route::post('refresh', 'TokenController@refresh');

Route::get('bots', 'HostController@bots');


Route::get('jobs/{job}', 'JobController@show');
//Route::put('jobs/{job}', 'JobController@update');