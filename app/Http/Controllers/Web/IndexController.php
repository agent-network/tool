<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as CommonController;

class IndexController extends CommonController
{
    /**
     * ダッシュボード
     *
     * @param  Request $request 
     * @return mixed   $data
     */
    public function index( Request $request )
    {
        // ログインチェック
        self::isNotLoginRedirect();
        self::setParamater( $request, 'ダッシュボード', 'ダッシュボード',
                            config( 'product.page.menu_id.dashboard' ), config( 'product.auth.level.normal.id' ) );

        if ( self::$setting['user']['level'] != config( 'product.auth.level.system.id' ) )
        {
            return redirect( '/mail' );
        }

        return view( 'index',
                     [
                         'setting' => self::$setting,
                         'time'    => time(),
                     ]
                   );
    }
}
