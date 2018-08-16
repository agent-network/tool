<?php

namespace App\Http\Controllers\Web\Manage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as CommonController;

use App\Eloquents\Mysql\SAdminUser;

use App\Utilities\CommonUtility;

class IndexController extends CommonController
{
    private static $sess_index_key  = 'manage_index_search';

    /**
     * コンストラクタ
     *
     * @param void
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * ユーザ一覧
     *
     * @param  Request $request 
     * @return mixed   $data
     */
    public function index( Request $request, $page=1 )
    {
        // ログインチェック
        self::isNotLoginRedirect();
        // パラメータ設定
        self::setParamater( $request, '管理者管理', '管理者一覧',
                            config( 'product.page.menu_id.manage' ), config( 'product.auth.level.system.id' ) );

        // 初期化するかどうか
        if ( isset( $_SERVER['HTTP_REFERER'] ) &&
             !preg_match( '/' . addcslashes( self::$setting['pager_uri'], '/' ) . '/', $_SERVER['HTTP_REFERER'] ) )
        {
            $request->session()->forget( self::$sess_index_key );
        }

        $mode = $request->input( 'mode' );
        $arr_post = self::convertData( $request->all() );

        switch ( $mode )
        {
            case 'search':
                $page = 1;
                $arr_search = $arr_post;
                $request->session()->put( self::$sess_index_key, $arr_search );
                return redirect( 'user' );
                break;
            case 'delete':
                self::deleteData( $arr_post );
                break;
            case 'csv_download':
                break;
            default:
                break;
        }
        $arr_search = $request->session()->has( self::$sess_index_key ) ?
                      $request->session()->get( self::$sess_index_key ) : [];

        $arr_user = SAdminUser::getListData( $page, $arr_search );

        return view( 'manage/index',
                     [
                         'setting' => self::$setting,
                         'data'    => $arr_user,
                         'search'  => $arr_search,
                         'time'    => time(),
                     ]
                   );
    }


    /**
     * ユーザの削除
     *
     * @param  array $arr_user_id ユーザIDの配列
     * @return void
     */
    private static function deleteData( $arr_post=[] )
    {
        if ( isset( $arr_post['admin_user_ids'] ) )
        {
            foreach ( $arr_post['admin_user_ids'] as $admin_user_id )
            {
                SAdminUser::deleteDataById( $admin_user_id );
            }
        }
    }
}
