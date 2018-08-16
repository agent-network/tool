<?php

namespace App\Eloquents\Mysql;

class SMailDelivery extends Mysql
{
    protected $table = 's_mail_deliveries';

    /**
     * データ抽出
     *
     * @param  integer $page       ページ数
     * @param  array   $arr_search 抽出条件
     * @return array   $data       抽出データとページャ設定パラメータ
     */
    public static function getListData( $page=1, $arr_search=[] )
    {
        $obj_model = self::where( 'delete_flg', '=', config( 'product.common.flag.off' ) );

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
                                         $query->where( 'title', 'like', "%{$word}%" )
                                               ->orwhere( 'body', 'like', "%{$word}%" );
                                     });
                    }
                    break;
                case 'id':
                case 'regist_admin_user_id':
                    $obj_model = $obj_model->where( $key, '=', $value );
                    break;
                case 'reserve_date':
                    $obj_model = $obj_model->where( $key, '>=', $value . ' 00:00:00' )
                                           ->where( $key, '<=', $value . ' 23:59:59' );
                    break;
                case 'delivery_type':
                case 'reserve_flg':
                case 'status':
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

        if ( count( $data ) > 0 )
        {
            foreach ( $data as $key => $value )
            {
                $data[$key]['delivery_count'] = SMailDeliveryAddress::getDeliveryCount( $value['id'] );
                $data[$key]['open_count']     = SMailDeliveryAddress::getOpenCount( $value['id'] );
            }
        }

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
     * @param  integer $mail_delivery_id メール配信ID
     * @return array   $data             抽出データ
     */
    public static function getDataById( $mail_delivery_id=NULL )
    {
        $obj_model = self::where( 'delete_flg', '=', config( 'product.common.flag.off' ) )
                         ->where( 'id', '=', $mail_delivery_id )
                         ->first();

        return is_object( $obj_model ) ? $obj_model->toArray() : [];
    }


    /**
     * IDを条件にレコードの論理削除
     *
     * @param  integer $mail_delivery_id メール配信ID
     * @return void
     */
    public static function deleteDataById( $mail_delivery_id=NULL )
    {
        $obj_model = self::find( $mail_delivery_id );
        $obj_model->delete_flg = config( 'product.common.flag.on' );
        $obj_model->save();
    }


    /**
     * レコードの登録・更新
     *
     * @param  array   $data             対象データ配列
     * @return integer $mail_delivery_id メール配信ID
     */
    public static function saveData( $data=[], $mail_delivery_id=NULL )
    {
        $obj_model = NULL !== $mail_delivery_id ? self::find( $mail_delivery_id ) : new self;

        if ( $data['reserve_flg'] == config( 'product.mail_delivery.reserve_flg.on.id' ) )
        {
            $reserve_date = $data['reserve_year'] . '-' . sprintf( '%02d', $data['reserve_month'] )
                          . '-' . sprintf( '%02d', $data['reserve_day'] ) . ' ' . sprintf( '%02d', $data['reserve_hour'] )
                          . ':' .  sprintf( '%02d', $data['reserve_minute'] ) . ':00';
            $obj_model->reserve_date = $reserve_date;
        }
        else
        {
            $obj_model->reserve_date = NULL;
        }

        $obj_model->from_email        = $data['from_email'];
        $obj_model->title             = $data['title'];
        $obj_model->body              = $data['body'];
        $obj_model->delivery_type     = $data['delivery_type'];
        $obj_model->reserve_flg       = $data['reserve_flg'];
        $obj_model->original_csv_name = $data['csv_info']['original'];
        $obj_model->delivery_csv_file = $data['csv_info']['save'];
        for ( $i = 0; $i < config( 'product.mail_delivery.common.attachment_file_number' ); $i++ )
        {
            $original_name = 'original_name' . ( $i + 1 );
            $attachment_file = 'attachment_file' . ( $i + 1 );
            if ( isset( $data['attachment_info'][$i] ) )
            {
                
                $obj_model[$original_name]   = $data['attachment_info'][$i]['original'];
                $obj_model[$attachment_file] = $data['attachment_info'][$i]['save'];
            }
            else
            {
                $obj_model[$original_name]   = NULL;
                $obj_model[$attachment_file] = NULL;
            }
        }
        $obj_model->save();

        return $obj_model->id;
    }


    /**
     * ステータスの更新
     *
     * @param  integer $status           ステータス
     * @param  integer $mail_delivery_id メール配信ID
     * @return void
     */
    public static function changeStatus( $status=NULL, $mail_delivery_id=NULL )
    {
        $obj_model = self::find( $mail_delivery_id );
        $obj_model->status = $status;
        $obj_model->save();
    }


    /**
     * 対象のメールデータを適切な状態へ変換して返却する
     *
     * @param  integer $mail_delivery_id メール配信ID
     * @return array   $data
     */
    public static function getTargetData( $mail_delivery_id=NULL )
    {
        $arr_mail = self::getDataById( $mail_delivery_id );
        if ( !empty( $arr_mail ) )
        {
            if ( !empty( $arr_mail['reserve_date'] ) )
            {
                list( $date, $time ) = explode( ' ', $arr_mail['reserve_date'] );
                list( $year, $month, $day )  = explode( '-', $date );
                list( $hour, $minute, $sec ) = explode( ':', $time );
                $arr_mail['reserve_year']   = $year;
                $arr_mail['reserve_month']  = intval( $month );
                $arr_mail['reserve_day']    = intval( $day );
                $arr_mail['reserve_hour']   = intval( $hour );
                $arr_mail['reserve_minute'] = intval( $minute );
            }
            else
            {
                $arr_mail['reserve_year']   = NULL;
                $arr_mail['reserve_month']  = NULL;
                $arr_mail['reserve_day']    = NULL;
                $arr_mail['reserve_hour']   = NULL;
                $arr_mail['reserve_minute'] = NULL;
            }

            for ( $i = 0; $i < config( 'product.mail_delivery.common.attachment_file_number' ); $i++ )
            {
                if ( !empty( $arr_mail['original_name' . ($i + 1 )] ) )
                {
                    $arr_mail['attachment_info'][$i]['original'] = $arr_mail['original_name' . ($i + 1 )];
                    $arr_mail['attachment_info'][$i]['save'] = $arr_mail['attachment_file' . ($i + 1 )];
                }
            }

            $arr_mail['csv_info']['original'] = $arr_mail['original_csv_name'];
            $arr_mail['csv_info']['save'] = $arr_mail['delivery_csv_file'];
        }

        return $arr_mail;
    }
}
