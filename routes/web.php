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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::match(['get','post'],'/create_database', 'HomeController@createDatabase')->name('create_database');
Route::get('/drop_database/{$db_name}', 'HomeController@dropDatabase')->name('drop_database');
Route::get('{db_name}/details', 'HomeController@dbDetails')->name('db_details');