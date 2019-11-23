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

//Route::get('/', function () {
//    return view('welcome');
//});

Route::group([
    'middleware'=>'api.throttle',
    'limit'=>config('app.rate_limits.sign.limit'),
    'expires'=>config('app.rate_limits.sign.expires'),
],function(){
    //短信验证码
    Route::post('verifyCode', 'VerificationCodeController@store')
        ->name('verificationCodes.store');
    Route::post('users','UsersController@store')->name('users.store');
});
Route::get('/', 'PagesController@root')->name('root');
//['middleware' => ['auth', 'verified']
