<?php

namespace App\Utilities;

use Carbon\Carbon;

use App\Eloquents\Mysql\MOccupation;
use App\Eloquents\Mysql\MPrefecture;
use App\Eloquents\Mysql\MRank;

class CommonUtility
{
    /**
     * 都道府県リストの取得
     *
     * @param  void
     * @return array
     */
    public static function getPrefecture()
    {
        return MPrefecture::getListData();
    }


    /**
     * 業種リストの取得
     *
     * @param  void
     * @return array
     */
    public static function getOccupation()
    {
        return MOccupation::getListData();
    }


    /**
     * ユーザランクリストの取得
     *
     * @param  void
     * @return array
     */
    public static function getRank()
    {
        return MRank::getListData();
    }


    /**
     * 年齢の取得
     *
     * @param  string $birthday Y-m-d形式の誕生日
     * @return array  $data     年齢とY年m月d日形式の誕生日
     */
    public static function getAge( $birthday=NULL )
    {
        $age   = NULL;
        $year  = NULL;
        $month = NULL;
        $day   = NULL;
        if ( NULL != $birthday )
        {
            list( $year, $month, $day ) = explode( '-', $birthday );
            $age = floor( ( intval( Carbon::now()->format( 'Ymd' ) ) - intval( $year . $month . $day ) ) / 10000 );
            $birthday = $year . '年' . $month . '月' . $day . '日';
        }
        return [
            'age'         =>  $age,
            'birthday'    => $birthday,
            'birth_year'  => $year,
            'birth_month' => intval( $month ),
            'birth_day'   => intval( $day )
        ];
    }


    /**
     * 年のリストを取得
     *
     * @param  integer $start 開始年
     * @param  integer $end   終了年
     * @return array   $data  年のリスト
     */
    public static function getYear( $start=NULL, $end=NULL )
    {
        $end = NULL === $end ? Carbon::now()->format( 'Y' ) : $end;
        $start = NULL === $start ? $end - 100 : $start;

        $data = [];
        for ( $i = $start; $i <= $end; $i++ )
        {
            $data[] = [ 'id' => $i, 'name' => $i ];
        }
        return $data;
    }


    /**
     * 月のリストを取得
     *
     * @param void
     * @return array $data 月のリスト
     */
    public static function getMonth()
    {
        $data = [];
        for ( $i = 1; $i <= 12; $i++ )
        {
            $data[] = [ 'id' => $i, 'name' => $i ];
        }
        return $data;
    }


    /**
     * 日のリストを取得
     * オプションで最後の日を「月末」のリストを返す
     *
     * @param  integer $interval 間隔
     * @param  bool    $option   最後の日を「月末」とするかどうか
     * @return array   $data     日のリスト
     */
    public static function getDay( $interval=1, $option=FALSE )
    {
        $data = [];
        if ( !$option )
        {
            for ( $i = $interval; $i <= 31; $i += $interval )
            {
                $data[] = [ 'id' => $i, 'name' => $i ];
            }
        }
        else
        {
            for ( $i = $interval; $i <= 29; $i += $interval )
            {
                $data[] = [ 'id' => $i, 'name' => $i . '日' ];
            }
            $data[] = [ 'id' => 99, 'name' => '月末' ];
        }
        return $data;
    }


    /**
     * 時のリストを取得
     *
     * @param  vlid
     * @return array $data 0～23のリスト
     */
    public static function getHour()
    {
        $data = [];
        for ( $i = 0; $i <= 23; $i++ )
        {
            $data[] = [ 'id' => strval( $i ), 'name' => $i ];
        }
        return $data;
    }


    /**
     * 分のリストを取得
     *
     * @param  integer $interval 間隔
     * @return array   $data     分のリスト
     */
    public static function getMinute( $interval=1 )
    {
        $data = [];
        for ( $i = 0; $i <= 59; $i += $interval )
        {
            $data[] = [ 'id' => strval( $i ), 'name' => sprintf( '%02d', $i ) ];
        }
        return $data;
    }


    /**
     * 半角英数のランダム文字列を取得
     *
     * @param  integer $length 取得したい文字数
     * @return string  $string 文字
     */
    public static function getRandomString( $length=10 )
    {
        $chars = config( 'product.common.password.string' );

        $string = NULL;
        for ( $i = 0; $i < $length; $i++ )
        {
            $string .= $chars[ mt_rand( 0, 61 ) ];
        }
        return $string;
    }


    /**
     * 質問タイプを取得
     *
     * @param void
     * @return array 質問タイプのリスト
     */
    public static function getQuestionType()
    {
        return [
            [ 'id' => '1', 'name' => 'テキストボックス' ],
            [ 'id' => '2', 'name' => 'テキストエリア' ],
            [ 'id' => '3', 'name' => 'セレクトボックス' ],
            [ 'id' => '4', 'name' => 'ラジオボタン' ],
            [ 'id' => '5', 'name' => 'チェックボックス' ],
            [ 'id' => '6', 'name' => 'メールアドレス' ],
        ];
    }


    public static function getShortUrl( $before_url=NULL )
    {
        $curl = curl_init() ;
        curl_setopt( $curl, CURLOPT_URL ,
                     config( 'product.common.google.url_api.url' ) . config( 'product.common.google.url_api.key' ) );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, [ 'Content-type: application/json' ] );        # JSONの送信
        curl_setopt( $curl, CURLOPT_CUSTOMREQUEST , 'POST' );                                  # POSTメソッド
        curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( [ 'longUrl' => $before_url ] ) ); # 送信するJSONデータ
        curl_setopt( $curl, CURLOPT_HEADER, 1 );                                               # ヘッダーを取得する
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );                                   # 証明書の検証を行わない
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );                                    # 結果を文字列で返す
        curl_setopt( $curl, CURLOPT_TIMEOUT, 15 );                                             # タイムアウトの秒数
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION , true );                                   # リダイレクト先を追跡するか？
        curl_setopt( $curl, CURLOPT_MAXREDIRS, 5 );                                            # 追跡する回数
        $res1 = curl_exec( $curl );
        $res2 = curl_getinfo( $curl );
        curl_close( $curl );

        // 取得したデータ
        $json = substr( $res1, $res2['header_size'] );      # 取得したデータ(JSONなど)
        $header = substr( $res1, 0, $res2['header_size'] ); # レスポンスヘッダー (検証に利用したい場合にどうぞ)

        // 取得したJSONをオブジェクトに変換
        $obj = json_decode( $json );

        // 短縮URLを取得
        $shorten_url = $before_url;

        if ( isset( $obj->id ) && !empty( $obj->id ) )
        {
            $shorten_url = $obj->id;
        }

        return $shorten_url;
    }
}
