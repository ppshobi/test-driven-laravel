<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/concerts/{id}', 'ConcertsController@show')->name('concerts.show');
Route::post('/concerts/{id}/orders', 'ConcertOrdersController@store');
Route::get('/orders/{confirmation_number}', 'OrdersController@show');

Route::post('/login', 'Auth\LoginController@login');
Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/logout', 'Auth\LoginController@logout');


Route::group([
    'middleware' => 'auth',
    'prefix' => 'backstage',
    'namespace' => 'Backstage'
], function () {
    Route::get('/concerts', 'ConcertsController@index');
    Route::post('/concerts', 'ConcertsController@store');
    Route::get('/concerts/new', 'ConcertsController@create');
});
