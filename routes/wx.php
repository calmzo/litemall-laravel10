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
    Route::post('h5pay', 'OrderController@h5pay');
    Route::any('list', 'OrderController@list');
    Route::any('detail', 'OrderController@detail');
    Route::any('submit', 'OrderController@submit');
    Route::any('cancel', 'OrderController@cancel');
    Route::any('refund', 'OrderController@refund');
    Route::any('delete', 'OrderController@delete');
    Route::any('confirm', 'OrderController@confirm');
});


Route::prefix('share')->group(function () {
    Route::get('qrcode', 'GrouponController@createGrouponShareImage');
});

Route::prefix('home')->group(function () {
    Route::get('index', 'HomeController@index');
});

Route::get('home/redirectShareUrl', 'HomeController@redirectShareUrl')->name('home.redirectShareUrl');
//订单模块--购物车
Route::prefix('cart')->group(function () {
    Route::post('add', 'CartController@add');
    Route::post('fastadd', 'CartController@fastadd');
    Route::any('countProduct', 'CartController@countProduct');
    Route::post('update', 'CartController@update');
    Route::post('delete', 'CartController@delete');
    Route::post('checked', 'CartController@checked');
    Route::any('index', 'CartController@index');
    Route::any('goodscount', 'CartController@goodscount');
    Route::get('checkout', 'CartController@checkout');
});

//商品模块--优惠券
Route::prefix('coupon')->group(function () {
    Route::any('list', 'CouponController@list');
    Route::any('mylist', 'CouponController@mylist');
    Route::any('receive', 'CouponController@receive');
});

//商品模块--团购
Route::prefix('groupon')->group(function () {
    Route::get('list', 'GrouponController@list');
    Route::get('test', 'GrouponController@test');
});


//用户模块--地址
Route::prefix('address')->group(function () {
    Route::get('list', 'AddressController@list');
    Route::post('save', 'AddressController@save');
    Route::post('delete', 'AddressController@delete');
});
