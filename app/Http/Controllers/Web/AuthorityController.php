<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as CommonController;

class AuthorityController extends CommonController
{
    const SESSID = 'auth_redirect_url';

    /**
     * ログイン
     *
     * @param  Request $request 
     * @return mixed   $data
     */
    public function signIn( Request $request )
    {
        // ログインチェック
        self::isLoginRedirect();
        // パラメータ設定
        self::setParamater( $request, 'ログイン', 'ログイン',
                            config( 'product.page.menu_id.dashboard' ), config( 'product.auth.level.normal.id' ) );

        $mode = $request->input( 'mode' );
        $message = NULL;

        if ( $mode == 'login' )
        {
            if ( self::login( $request->all() ) )
            {
                $url = $request->session()->has( self::SESSID ) ?
                       urldecode( $request->session()->get( self::SESSID ) ) : '/';
                $request->session()->forget( self::SESSID );
                return redirect( $url );
            }
            $message = "メールアドレス又はパスワードが間違っています";
        }
        else
        {
            $request->session()->put( self::SESSID, $request->input( 'r' ) );
        }

        return view( 'login',
                     [
                         'setting' => self::$setting,
                         'time'    => time(),
                         'message' => $message,
                     ]
                   );
    }


    /**
     * ログアウト
     *
     * @param  void
     * @return redirect
     */
    public function signOut()
    {
        self::logout();
        return redirect( 'login' );
    }
}
