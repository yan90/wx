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
Route::post('/wx','TextController@checkSignature');  //接口微信
Route::get('/wx/token','TextController@token');  //access_token
//Route::get('/tell','TextController@tell');  //postman测试
//Route::post('/tell2','TextController@tell2');  //postman测试
Route::get('/custom','TextController@custom');  //自定义菜单

//TEST 路由分组
//Route::prefix('/text')get()->group(function (){
//
//});
Route::get('getweather','TextController@getweather');
Route::get('/guzzle',"TextController@guzzle");  //guzzle 测试  GET
Route::get('/guzzle2',"TextController@guzzle2");  //guzzle 测试  POST

