<?php

namespace App\Eloquents\Mysql;

use Carbon\Carbon;

class SMailDeliveryAddress extends Mysql
{
    protected $table = 's_mail_delivery_addresses';

    protected $guarded = [
        'id'
    ];


    /**
     * 配信者数を取得
     *
     * @param  integer $mail_delivery_id 配信ID
     * @return integer $count            配信者数
     */
    public static function getDeliveryCount( $mail_delivery_id=NULL )
    {
        return self::where( 'mail_delivery_id', '=', $mail_delivery_id )
                   ->where( 'status', '=', config( 'product.common.flag.on' ) )
                   ->where( 'delete_flg', '=', config( 'product.common.flag.off' ) )
                   ->count();
    }


    /**
     * 開封者数を取得
     *
     * @param  integer $mail_delivery_id 配信ID
     * @return integer $count            配信者数
     */
    public static function getOpenCount( $mail_delivery_id=NULL )
    {
        return self::where( 'mail_delivery_id', '=', $mail_delivery_id )
                   ->where( 'open_flg', '=', config( 'product.common.flag.on' ) )
                   ->where( 'status', '=', config( 'product.common.flag.on' ) )
                   ->where( 'delete_flg', '=', config( 'product.common.flag.off' ) )
                   ->count();
    }


    /**
     * 開封フラグをセット
     *
     * @param  integer $id ID
     * @return void
     */
    public static function setOpenFlg( $id=NULL )
    {
        $obj_model = self::find( $id );
        if ( is_object( $obj_model ) )
        {
            $obj_model->open_flg = config( 'product.common.flag.on' );
            $obj_model->open_date = Carbon::now();
            $obj_model->save();
        }
    }


    /**
     * 送信アドレスを登録
     *
     * @param  integer $mail_delivery_id 配信ID 
     * @param  array   $email            配信アドレス 
     * @return integer $id               登録アドレスID
     */
    public static function saveData( $mail_delivery_id=NULL, $email=NULL )
    {
        $obj_model = self::firstOrNew( [ 'mail_delivery_id' => $mail_delivery_id, 'email' => $email ] );
        $obj_model->mail_delivery_id = $mail_delivery_id;
        $obj_model->email            = $email;
        $obj_model->save();

        return $obj_model->id;
    }
}
