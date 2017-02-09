<?php

use Illuminate\Http\Request;

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


Route::get('/', function () {
    return view('index');
});

Route::group(['prefix' => 'api'], function()
{
    Route::resource('authenticate', 'AuthenticateController', ['only' => ['index']]);
    Route::post('authenticate', 'AuthenticateController@authenticate');

//    Route::any('/test', function () {
//
//
//
//        $response = array('status' => 'ok', 'productList' => 'product list');
//
//        return \Response::json($response, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
//    });

    Route::any('test', 'AuthenticateController@test');
});



Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
