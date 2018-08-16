<?php

namespace App\Eloquents\Mysql;

class SBounceMail extends Mysql
{
    protected $table = 's_bounce_mails';

    /**
     * レコードの登録・更新
     *
     * @param  array   $data             対象データ配列
     * @return integer $mail_delivery_id メール配信ID
     */
    public static function saveData( $server, $log_date, $que=NULL, $data=[] )
    {
        $obj_model = new self;
        $obj_model->server      = $server;
        $obj_model->log_date    = $log_date;
        $obj_model->que         = $que;
        $obj_model->date        = $data['date'];
        $obj_model->from        = $data['from'];
        $obj_model->email       = $data['email'];
        $obj_model->status      = $data['status'];
        $obj_model->description = $data['description'];
        $obj_model->message     = $data['message'];
        $obj_model->save();

        return $obj_model->id;
    }


    /**
     * ログのリセット
     * 
     * @param  string $server
     * @param  string $log_date
     * @return void
     */
    public static function resetData( $server=NULL, $log_date=NULL )
    {
        self::where( 'server', '=', $server )
            ->where( 'log_date', '=', $log_date )
            ->delete();
    }
}
