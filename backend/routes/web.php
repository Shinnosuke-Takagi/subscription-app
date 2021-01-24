<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/{any?}', function(){
  return view('index');
})->where('any', '.+');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('subscription', 'Subscriptions\PaymentController@index')->name('subscriptions.index');
Route::post('subscription/subscribe', 'Subscriptions\PaymentController@subscribe')->name('subscriptions.subscribe');
Route::post('subscription/updateCard', 'Subscriptions\PaymentController@updateCard')->name('subscriptions.updateCard');
Route::post('subscription/changePlan', 'Subscriptions\PaymentController@changePlan')->name('subscriptions.changePlan');
Route::post('subscription/cancel', 'Subscriptions\PaymentController@cancel')->name('subscriptions.unsubscribe');
Route::post('subscription/resume', 'Subscriptions\PaymentController@resume')->name('subscriptions.resume');
