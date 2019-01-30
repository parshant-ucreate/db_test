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

Route::post('/2fa', function () {
    return redirect(URL()->previous());
})->name('2fa')->middleware('2fa');

Route::get('/complete-registration', 'Auth\RegisterController@completeRegistration');

//Route::get('/register', 'Auth\RegisterController@disableRegistration')->name('disableRegistration');

Route::get('/home', 'HomeController@index')->name('home');

Route::match(['get','post'],'/create_database', 'HomeController@createDatabase')->name('create_database');
Route::get('/drop_database/{db_name}', 'HomeController@dropDatabase')->name('drop_database');
Route::get('{db_name}/details', 'HomeController@dbDetails')->name('db_details');
Route::get('db/logs', 'HomeController@showDatabaseLogs')->name('db_logs');
Route::get('{db_name}/backup', 'HomeController@backupDatabase')->name('backup_database');
Route::match(['get','post'],'{db_name}/import', 'HomeController@importDatabase')->name('import_database');

Route::delete('{db_name}/import/{id}', 'HomeController@deleteDatabaseBackup');
Route::get('/download_backup/{filename}', 'HomeController@downloadBackup');

Route::match(['get','post'],'{db_name}/import_file', 'HomeController@importDatabaseFile')->name('import_file');

Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
Route::get('cron_backup', 'HomeController@backupDatabaseCron')->name('cron_backup');
Route::get('db/reports', 'HomeController@getDbReports')->name('db_reports');
Route::match(['get','post'],'/backup_interval/{db}', 'HomeController@backupInterval')->name('backup_interval');
