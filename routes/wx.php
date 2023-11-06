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

//用户模块
Route::prefix('auth')->group(function () {
    Route::post('register', 'AuthController@register');
    Route::post('regCaptcha', 'AuthController@regCaptcha');
    Route::post('login', 'AuthController@login');
    Route::get('info', 'AuthController@info'); //用户信息
    Route::post('reset', 'AuthController@reset');//重置密码
    Route::post('resetPhone', 'AuthController@resetPhone'); //重置手机号
    Route::post('profile', 'AuthController@profile'); //编辑信息
    Route::post('logout', 'AuthController@logout'); //退出登录

});

Route::prefix('user')->group(function () {
    Route::get('index', 'UserController@index');
});

Route::prefix('catalog')->group(function () {
    Route::get('index', 'CatalogController@index');
    Route::get('current', 'CatalogController@current');
});

Route::prefix('brand')->group(function () {
    Route::get('list', 'BrandController@list');
    Route::get('detail', 'BrandController@detail');
});

Route::prefix('goods')->group(function () {
    Route::get('count', 'GoodsController@count');
    Route::get('category', 'GoodsController@category'); //商品分类类目
    Route::get('list', 'GoodsController@list'); //
    Route::get('detail', 'GoodsController@detail'); //

});

Route::prefix('order')->group(function () {
    Route::get('h5pay', 'OrderController@h5pay');
});


Route::prefix('share')->group(function () {
    Route::get('qrcode', 'GrouponController@createGrouponShareImage');
});

Route::prefix('home')->group(function () {
    Route::get('home/redirectShareUrl', 'HomeController@redirectShareUrl')->name('redirectShareurl');
    Route::get('home/index', 'HomeController@index');
});
