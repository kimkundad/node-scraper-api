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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// --- start move to client --- //
Route::post('ffp', 'API\ContentDetailController@index')->name('ffp'); // latest content
Route::post('ffp-test', 'API\ContentDetailController@testFFPStructure')->name('ffp-test');
Route::post('prediction', 'API\ContentDetailController@prediction')->name('prediction'); // same ffp, but get top of inner detail
Route::post('prediction-price', 'API\ContentDetailController@predictionPrice')->name('prediction-price'); // same prediction, but get graph
Route::post('dir-list', 'API\ContentDetailController@dirList')->name('dir-list'); // dir list
Route::post('content-detail', 'API\ContentDetailController@contentDetail')->name('content-detail');
Route::post('data-to-graph', 'API\ContentDetailController@dataToGraph')->name('data-to-graph'); // data to graph
Route::post('current-detail-content', 'API\ContentDetailController@currentDetailcontent')->name('current-detail-content');
// --- end move to client --- //

Route::post('all-column', 'API\SystemInfoController@allColumn')->name('all-column'); // all column
Route::post('data-table', 'API\SystemInfoController@dataTableDetail')->name('data-table-detail'); // data table detail

Route::post('main-price', 'API\DBProviderController@mainPrice')->name('main-price');
Route::post('graph-detail', 'API\DBProviderController@graphDetail')->name('graph-detail');
