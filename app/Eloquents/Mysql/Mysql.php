<?php

namespace App\Eloquents\Mysql;

use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

class Mysql extends Model
{
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
}
