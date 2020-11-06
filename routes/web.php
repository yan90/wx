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
Route::get('phpinfo',function (){
    echo phpinfo();
});
//Route::get('text1','TextController@text1');
Route::get('/wx','TextController@checkSignature');  //接口微信
Route::get('/wx/token','TextController@token');  //access_token


