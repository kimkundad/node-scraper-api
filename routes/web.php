<?php

use Illuminate\Support\Facades\Route;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'Web\WelcomeController@index');
// Route::get('/route-list', 'Web\WelcomeController@routeList');
Route::get('table-list', 'Web\WelcomeController@tableList');
Route::get('/test-as-dooball', function() {
    return view('test-as-dooball');
});

Route::get('ราคาบอล', 'Web\ContentDetailController@index'); // current content
Route::get('prediction', 'Web\ContentDetailController@prediction'); // current price
Route::get('ราคาบอลไหล', 'Web\ContentDetailController@inner'); // now inner
Route::get('content-detail', 'Web\ContentDetailController@contentDetail'); // content detail

Route::get('/log-html', 'Web\WelcomeController@logHtml');
Route::get('/log-laravel', 'Web\WelcomeController@logLaravel');
Route::get('/logos', 'Web\WelcomeController@logos');
Route::get('/logs-html', 'Web\WelcomeController@logsHtml');

Route::get('main-price/{latest_dir?}', 'Web\WelcomeController@mainPrice');
Route::get('chk-main-status/{latest_dir?}', 'Web\WelcomeController@checkScrapingStatus');
Route::get('graph-detail/{latest_id?}', 'Web\WelcomeController@graphDetail');
Route::get('chk-detail/{ids?}', 'Web\WelcomeController@checkDetailDatas');
Route::get('chk-league/{ids?}', 'Web\WelcomeController@checkLeagueDatas');

Route::get('/{table_name}', 'Web\WelcomeController@dataTableDetail'); // data in table
