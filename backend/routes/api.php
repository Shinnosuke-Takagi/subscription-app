<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
Route::get('/user', function() {
  return Auth::user();
})->name('user');

Route::get('/refresh-token', function (Request $request) {
  $request->session()->regenerateToken();

  return response()->json();
});

Route::post('/register', 'Auth\RegisterController@register')->name('register');
Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/setup', 'Subscriptions\PaymentController@setup')->name('setup');
Route::post('/registerCard', 'Subscriptions\PaymentController@registerCard')->name('registerCard');
Route::post('/changeDefaultCard', 'Subscriptions\PaymentController@changeDefaultCard')->name('changeDefaultCard');

Route::post('/subscribePlan', 'Subscriptions\PaymentController@subscribePlan')->name('subscribePlan');
