php<?php

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
Route::post('address', 'AddressController@createPolygon');
Route::get('user/address/{userId}', 'AddressController@getUserAddresses');
Route::get('address/{digital_address}', 'AddressController@getAddress');
Route::get('address', 'AddressController@getAllAddresses');
Route::post('register', 'Api\ApiRegistrationController@store');
Route::post('login', 'Api\ApiSessionsController@store');
Route::get('user/{userId}', 'Api\ApiSessionsController@getAuthUser');
Route::get('logout', 'Api\ApiSessionsController@destroy');

Route::get('sparql/{location}', 'Sparql@getLocation');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
