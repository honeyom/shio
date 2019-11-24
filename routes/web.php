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

    Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
    Route::put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
    Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');

});
Route::get('/', 'PagesController@root')->name('root');
Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
//['middleware' => ['auth', 'verified']
Route::redirect('/','/products')->name('root');
Route::get('products','PrductsController@index')->name('products.index');
Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
Route::get('products/favorites', 'ProductsController@favorites')->name('products.favorites');

