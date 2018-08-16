<?php

namespace App\Eloquents\Mysql;

use DB;
use Auth;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SAdminUser extends Authenticatable
{
    use Notifiable;
    protected $table = 's_admin_users';

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    const CREATED_AT = 'regist_admin_user_date';
    const UPDATED_AT = 'update_admin_user_date';

    public $timestamps = TRUE;

    protected static function boot()
    {
        parent::boot();

        self::creating( function( $entity )
        {
            $entity->regist_admin_user_id = Auth::id();
            $entity->update_admin_user_id = Auth::id();
        });

        self::updating( function( $entity )
        {
            $entity->update_admin_user_id = Auth::id();
        });
    }


    /**
     * ページャー設定値の取得
     *
     * @param integer $disp ページャーの表示数
     * @param integer $page 現在のページ数
     * @param integer $max ページャーの最大値
     * @return array
     */
    protected static function setPager( $disp, $page, $max )
    {
        $disp = $max;
        $ave  = 2;

        $next = $page + 1;
        $prev = $page - 1;

        $start = ( $page - $ave > 0 ) ? ( $page - $ave ) : 1;           //始点
        $end   = ( ( $page + $ave ) < $max ) ? ( $page + $ave ) : $max; //終点
        $start = ( ( $max >= $end ) && ( $end - $start < $ave * 2 ) && ( $page - $ave > 0 ) ) ? $end - 4 : $start;
        $end   = ( ( $max >= $end ) && ( $end - $start < $ave * 2 ) && ( ( $page + $ave ) < $max ) ) ? $start + 4 : $end;
        $start = ( $start < 1 ) ? 1 : $start;
        $end   = ( $end > $max ) ? $max : $end;

        return [
            'start' => $start,
            'end'   => $end,
            'prev'  => $prev,
            'next'  => $next,
            'ave'   => $ave,
            'max'   => $max,
            'page'  => $page,
        ];
    }


    /**
     * データ抽出
     *
     * @param  integer $page       ページ数
     * @param  array   $arr_search 抽出条件
     * @return array   $data       抽出データとページャ設定パラメータ
     */
    public static function getListData( $page=1, $arr_search=[] )
    {
        $obj_model = self::where( 'status', '=', config( 'product.common.flag.on' ) )
                         ->where( 'delete_flg', '=', config( 'product.common.flag.off' ) );

        foreach ( $arr_search as $key => $value )
        {
            if ( $value == '' )
            {
                continue;
            }
            $key = preg_replace( '/^search_/', '', $key );
            switch ( $key )
            {
                case 'keyword':
                    $arr_word = explode( ' ', mb_convert_kana( $value, 'KVas' ) );
                    foreach ( $arr_word as $word )
                    {
                        $obj_model = $obj_model->where( function( $query ) use ( $word )
                                     {
                                         $query->where( 'last_name', 'like', "%{$word}%" )
                                               ->orwhere( 'first_name', 'like', "%{$word}%" )
                                               ->orwhere( 'last_name_kana', 'like', "%{$word}%" )
                                               ->orwhere( 'first_name_kana', 'like', "%{$word}%" )
                                               ->orwhere( 'email', 'like', "%{$word}%" )
                                               ->orwhere( 'address01', 'like', "%{$word}%" )
                                               ->orwhere( 'address02', 'like', "%{$word}%" )
                                               ->orwhere( 'tel01', 'like', "%{$word}%" )
                                               ->orwhere( 'tel02', 'like', "%{$word}%" )
                                               ->orwhere( 'tel03', 'like', "%{$word}%" )
                                               ->orwhere( 'mobile01', 'like', "%{$word}%" )
                                               ->orwhere( 'mobile02', 'like', "%{$word}%" )
                                               ->orwhere( 'mobile03', 'like', "%{$word}%" );
                                     });
                    }
                    break;
                case 'id':
                case 'prefecture_id':
                case 'occupation_id':
                case 'rank_id':
                    $obj_model = $obj_model->where( $key, '=', $value );
                    break;
                case 'gender':
                    $obj_model = $obj_model->whereIn( $key, $value );
                    break;
                default:
                    break;
            }
        }

        $limit = config( 'product.page.number50' );
        $offset = $limit * ( $page - 1 );

        // 対象レコードの件数を取得
        $total = $obj_model->count();

        // 対象のレコードを取得
        $obj_model = $page > 0 ? $obj_model->skip( $offset )->take( $limit ) : $obj_model;
        $data = $obj_model->orderBy( 'id', 'DESC' )
                          ->get();
        $data = $total > 0 ? $data->toArray() : [];

        // 表示する最初と最後の件数を取得
        $start = ( $page - 1 ) * $limit + 1;
        $end   = $page * $limit < $total ? $page * $limit : $total;

        // ページ設定
        $pager = self::setPager( config( 'product.page.number50' ), $page, ceil( $total / $limit ) );

        return [ 'start' => $start, 'end' => $end, 'total' => $total, 'list' => $data, 'pager' => $pager ];
    }


    /**
     * IDを条件にデータ抽出
     *
     * @param  integer $admin_user_id ユーザID
     * @return array   $data          抽出データ
     */
    public static function getDataById( $admin_user_id=NULL )
    {
        $obj_model = self::where( 'status', '=', config( 'product.common.flag.on' ) )
                         ->where( 'delete_flg', '=', config( 'product.common.flag.off' ) )
                         ->where( 'id', '=', $admin_user_id )
                         ->select(
                             '*',
                             DB::raw( 'DATE_FORMAT( regist_admin_user_date, "%Y年%m月%d日" ) AS regist_admin_user_date_jp' )
                         )
                         ->first();

        return is_object( $obj_model ) ? $obj_model->toArray() : [];
    }


    /**
     * IDを条件にレコードの論理削除
     *
     * @param  integer $admin_user_id ユーザID
     * @return void
     */
    public static function deleteDataById( $admin_user_id=NULL )
    {
        $obj_model = self::find( $admin_user_id );
        $obj_model->leave_date = Carbon::now();
        $obj_model->delete_flg = config( 'product.common.flag.on' );
        $obj_model->save();
    }


    /**
     * レコードの登録・更新
     *
     * @param  array   $data          対象データ配列
     * @return integer $admin_user_id ユーザID
     */
    public static function saveData( $data=[], $admin_user_id=NULL )
    {
        $obj_model = NULL !== $admin_user_id ? self::find( $admin_user_id ) : new self;

        $obj_model->email           = $data['email'];
        $obj_model->last_name       = $data['last_name'];
        $obj_model->first_name      = $data['first_name'];
        $obj_model->last_name_kana  = $data['last_name_kana'];
        $obj_model->first_name_kana = $data['first_name_kana'];
        $obj_model->sub_email01     = empty( $data['sub_email01'] ) ? NULL : $data['sub_email01'];
        $obj_model->sub_email02     = empty( $data['sub_email02'] ) ? NULL : $data['sub_email02'];
        $obj_model->level           = $data['level'];
        $obj_model->save();

        return $obj_model->id;
    }


    /**
     * パスワードの設定
     *
     * @param  string  $password       パスワード
     * @param  integer $admin_user_id  ユーザID
     * @return void
     */
    public static function setPassword( $password=NULL, $admin_user_id=NULL )
    {
        $obj_model = self::find( $admin_user_id );
        $obj_model->password = password_hash( $password, PASSWORD_DEFAULT );
        $obj_model->save();
    }
}
