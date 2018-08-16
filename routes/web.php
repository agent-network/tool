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

Route::group( [ 'namespace' => 'Web' ], function()
{
    // メール配信
    Route::group( [ 'namespace' => 'Mail' ], function()
    {
        Route::group( [ 'prefix' => 'mail' ], function()
        {
            // 編集
            Route::get( 'open/{id}',  'OpenController@index' );
            // 詳細
            Route::group( [ 'prefix' => 'detail' ], function()
            {
                Route::get( '/', function()
                {
                    return redirect( '/mail' );
                });
                Route::get( '/{id}',  'IndexController@detail' );
                Route::post( '/{id}', 'IndexController@detail' );
            });
            // 編集
            Route::get( 'edit/{id}',  'IndexController@edit' );
            Route::post( 'edit/{id}', 'IndexController@edit' );
            // 新規登録
            Route::get( 'new',      'IndexController@edit' );
            Route::post( 'new',     'IndexController@edit' );
            // 一覧
            Route::get( '/{page}',  'IndexController@index' );
            Route::post( '/{page}', 'IndexController@index' );
            Route::get( '/',        'IndexController@index' );
            Route::post( '/',       'IndexController@index' );
        });
    });

    // 管理者
    Route::group( [ 'namespace' => 'Manage' ], function()
    {
        Route::group( [ 'prefix' => 'manage' ], function()
        {
            // 基本情報
            Route::group( [ 'prefix' => 'detail' ], function()
            {
                Route::get( '/', function()
                {
                    return redirect( '/manage' );
                });
                Route::get( 'edit/{id}',  'DetailController@edit' );
                Route::post( 'edit/{id}', 'DetailController@edit' );
                Route::get( 'new',        'DetailController@edit' );
                Route::post( 'new',       'DetailController@edit' );
                Route::get( '{id}',       'DetailController@index' );
                Route::post( '{id}',      'DetailController@index' );
            });
            // 一覧
            Route::get( '/{page}',  'IndexController@index' );
            Route::post( '/{page}', 'IndexController@index' );
            Route::get( '/',        'IndexController@index' );
            Route::post( '/',       'IndexController@index' );
        });
    });

    // ログイン
    Route::get( 'login',  'AuthorityController@signIn' );
    Route::post( 'login', 'AuthorityController@signIn' );
    // ログアウト
    Route::get( 'logout', 'AuthorityController@signOut' );

    // ダッシュボード
    Route::get( '/',  'IndexController@index' );
});
