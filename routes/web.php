<?php

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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('home');
    }

    return view('welcome');
});

Auth::routes();

Route::get('home', 'HomeController@index')
    ->name('home');

Route::get('bots/{bot}/delete', 'BotController@delete')->name('bots.delete');
Route::resource('bots', 'BotController');
Route::resource('clusters', 'ClusterController');
Route::resource('files', 'FileController');

Route::post('jobs/{job}/pass', 'JobController@pass')->name('jobs.pass');
Route::post('jobs/{job}/fail', 'JobController@fail')->name('jobs.fail');

Route::resource('jobs', 'JobController');

Route::get('jobs/create/file/{file}', 'JobFileController@create')
    ->name('jobs.create.file');

Route::post('jobs/file/{file}', 'JobFileController@store')
    ->name('jobs.file.store');

Route::get('hosts/requests', 'HostRequestController@index');
Route::get('hosts/requests/{host_request}', 'HostRequestController@show')
    ->name('hosts.requests.show');
Route::get('hosts', 'HostController@index');
Route::post('hosts', 'HostController@store')
    ->name('hosts.store');
