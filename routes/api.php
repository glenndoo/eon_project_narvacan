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

Route::group(['prefix' => 'mobile',  'middleware' => 'EonMiddleware'],function(){
    Route::group(['middleware'=> ['ApiGet']], function(){
        Route::get('/CreateEonToken', array(
            'as' => 'CreateEonToken',
            'uses' => 'EonController@CreateEonToken'
        ));
        Route::get('/FetchAllCards', array(
            'as' => 'FetchAllCards',
            'uses' => 'EonController@FetchAllCards'
        ));
        Route::get('/AdditionalDetails', array(
            'as' => 'AdditionalDetails',
            'uses' => 'EonController@AdditionalDetails'
        ));
    });

    Route::group(['middleware'=> ['ApiPost']], function(){
        Route::post('/createCustomerProfile', array(
            'as' => 'createCustomerProfile',
            'uses' => 'EonController@createCustomerProfile'
        ));
        Route::post('/CreateVirtualCard', array(
            'as' => 'CreateVirtualCard',
            'uses' => 'EonController@CreateVirtualCard'
        ));
        Route::post('/ActivateEonCard', array(
            'as' => 'ActivateEonCard',
            'uses' => 'EonController@ActivateEonCard'
        ));
        Route::post('/PullUserDetails', array(
            'as' => 'PullUserDetails',
            'uses' => 'EonController@PullUserDetails'
        ));
    });
    
});
