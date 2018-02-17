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

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('dashboard');
    }

    return view('welcome');
});

Auth::routes();

Route::get('dashboard', 'HomeController@index')
    ->name('dashboard');

Route::resource('bots', 'BotController');
Route::resource('clusters', 'ClusterController');
Route::resource('files', 'FileController');
Route::resource('jobs', 'JobController');

Route::get('jobs/create/file/{file}', 'JobFileController@create')
    ->name('jobs.create.file');

Route::post('jobs/file/{file}', 'JobFileController@store')
    ->name('jobs.file.store');
