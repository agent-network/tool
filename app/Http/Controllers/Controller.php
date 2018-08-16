<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static $setting = [
        'title'       => NULL,
        'subtitle'    => NULL,
        'request_uri' => NULL,
        'pager_uri'   => NULL,
        'menu_id'     => NULL,
        'user'        => NULL,
    ];


    /**
     * コンストラクタ
     */
    public function __construct()
    {
    }


    /**
     * ログイン
     *
     * @param array $data
     * @return bool
     */
    protected static function login( $data )
    {
        return Auth::attempt(
                   [
                       'email'      => $data['email'],
                       'password'   => $data['password'],
                       'status'     => 1,
                       'delete_flg' => 0
                   ]
               );
    }


    /**
     * ログアウト
     *
     * @param void
     * @return bool
     */
    protected static function logout()
    {
        Auth::logout();
    }


    /**
     * ログインチェック
     *
     * @param void
     * @return bool
     */
    protected static function isLogin()
    {
        return Auth::check();
    }


    /**
     * ログインチェック&リダイレクト
     * ログインしていない場合ログインページへリダイレクトさせる
     *
     * @param void
     * @return void
     */
    protected static function isNotLoginRedirect()
    {
        if ( !self::isLogin() )
        {
            header( 'location: /login/?r=' . urlencode( $_SERVER['REQUEST_URI'] ) );
            exit;
        }
    }


    /**
     * ログインチェック&リダイレクト
     * ログインしている場合TOPページへリダイレクトさせる
     *
     * @param void
     * @return void
     */
    protected static function isLoginRedirect()
    {
        if ( self::isLogin() )
        {
            return redirect( '/' );
        }
    }



    /**
     * ページャーに必要なURIを取得
     */
    protected static function getPagerUrl( Request $request )
    {
        $match = [];
        $uri = self::getRequestUrl( $request );
        return preg_replace( '/^\/\//', '/', preg_replace( '/\/[0-9]+$/', '', $uri ) );
    }


    /**
     * リクエストされたURIを取得
     */
    protected static function getRequestUrl( Request $request )
    {
        return '/' == $request->path() ? '' : '/' . $request->path();
    }


    /**
     * VIEWに必要なパラメータを設定
     */
    protected static function setParamater( Request $request, $title, $subtitle, $menu_id, $auth_level )
    {
        if ( self::isLogin() )
        {
            self::$setting['user'] = Auth::user()->toArray();
            if ( intval( $auth_level ) < intval( self::$setting['user']['level'] ) )
            {
                header( 'Location: /' );
                exit();
            }
        }
        self::$setting['title']       = $title;
        self::$setting['subtitle']    = $subtitle;
        self::$setting['request_uri'] = self::getRequestUrl( $request );
        self::$setting['pager_uri']   = self::getPagerUrl( $request );
        self::$setting['menu_id']     = $menu_id;
    }


    protected static function convertData( $data=[] )
    {
        foreach ( $data as $i => $column )
        {
            if ( is_object( $column ) )
            {
                continue;
            }
            else if ( !is_array( $column ) )
            {
                $data[$i] = mb_convert_kana( $column, 'KVas' );
            }
            else
            {
                foreach ( $column as $key => $value )
                {
                    if ( !is_array( $value ) )
                    {
                        $data[$i][$key] = mb_convert_kana( $value, 'KVas' );
                    }
                    else
                    {
                        foreach ( $value as $k => $v )
                        {
                            if ( !is_array( $v ) )
                            {
                                $data[$i][$key][$k] = mb_convert_kana( $v, 'KVas' );
                            }
                            else
                            {
                                $data[$i][$key][$k] = $v;
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }
}
