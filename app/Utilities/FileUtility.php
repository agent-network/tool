<?php
  
namespace App\Utilities;

use Log;
use Exception;

class FileUtility
{
    /**
     * CSVファイルをパースして配列にして返却する
     *
     * @param  string $path     ファイルパス
     * @return array  $arr_data CSVの中身の配列データ 
     */
    public static function parseCsv( $path=NULL, $subject_flg=FALSE )
    {
        $arr_file = [];
        $arr_data = [];
        $obj_file = new \SplFileObject( $path );
        $obj_file->setFlags( \SplFileObject::READ_CSV );

        // ファイルの中身の文字コードをUTF-8へ変換
        foreach ( $obj_file as $i => $line )
        {
            foreach ( $line as $key => $value )
            {
                $key = mb_convert_encoding( $key, config( 'product.file.encode.code2' ),
                                            '"' . config( 'product.file.encode.code1' ) . ','
                                          . config( 'product.file.encode.code2' ) . '"' );
                $arr_file[$i][$key] = mb_convert_encoding( $value, config( 'product.file.encode.code2' ),
                                                           '"' . config( 'product.file.encode.code1' ) . ','
                                                         . config( 'product.file.encode.code2' ) . '"' );
            }
        }

        if ( $subject_flg )
        {
            foreach ( $arr_file as $i => $line )
            {
                if ( 0 == $i )
                {
                    foreach ( $line as $j => $column )
                    {
                        $line[$j] = strtolower( $column );
                    }
                    $arr_subject = $line;
                }
                else
                {
                    if ( count( $arr_subject ) <> count( $line ) )
                    {
                        continue;
                    }
                    for ( $j = 0; $j < count( $line ); $j++ )
                    {
                        $arr_data[$i][$arr_subject[$j]] = $line[$j];
                    }
                }
            }
        }
        else
        {
            foreach ( $arr_file as $line )
            {
                if ( count( $line ) > 1 || !empty( $line[0] ) )
                {
                    $arr_data[] = $line;
                }
            }
        }
        return $arr_data;
    }


    /**
     * CSV書き込み
     *
     * @param array  $data    書き込むデータ
     * @param array  $subject 書き込むタイトル
     * @param string $name    ファイル名
     * @param bool   $save    TRUE:定数で指定した保存先へ保存、FALSE:ダウンロード処理を実施する。
     * @return mixed string  成功:ファイル名、失敗:FALSE
     */
    public static function writeCsv( $data=[], $subject=[], $name=NULL, $save=TRUE )
    {
        try
        {
            // 出力するCSVの文字コードを取得
            $from_encoding = config( 'product.file.encode.code2' );
            $to_encoding   = config( 'product.file.encode.code1' );
            // $dataの必須チェック
            if( empty( $data ) )
            {
                throw new Exception( '書き込みデータ必須エラー' );
            }
            //$nameの空チェック。空であればファイル名をタイムスタンプにする。
            if( empty( $name ) )
            {
                $name = time(). '.csv';
            }
            //$saveがTRUEなら定数で指定した保存先へ保存
            if( $save )
            {
                $file_path = config( 'product.file.download.csv_path' );
                $file_path .= $name;
            }
            else
            {
                $file_path = 'php://temp/'. $name;
            }
            $fp = fopen( $file_path, 'w+' );
            //CSVデータ配列の先頭行にタイトルを結合
            if( !empty( $subject ) ) {
                array_unshift( $data, $subject );
            }
            //ファイル書き込み
            foreach( $data as $line )
            {
                foreach ( $line as $k => $v )
                {
                    $line[$k] = mb_convert_encoding( $v, $to_encoding, $from_encoding );
                }
                fputcsv( $fp, $line );
            }
            //ファイルをダウンロードする場合はhttpヘッダ、CSV内容を出力
            if( !$save )
            {
                rewind( $fp );
                $csv = str_replace( PHP_EOL, "\r\n", stream_get_contents( $fp ) );
                fclose( $fp );
                // ヘッダ
                header( 'Content-Type: text/csv' );
                // ダイアログボックスに表示するファイル名
                header( 'Content-Disposition: attachment; filename='. $name );
                //ファイル出力
                echo $csv;
                //ダウンロードの場合ここで処理終了
                exit;
            }
            fclose( $fp );
        }
        catch ( Exception $e )
        {
            Log::error( $e->getMessage() );
            return false;
        }

        return $name;
    }


    /**
     * ファイルをzip形式で圧縮する
     *
     * @param  string $zipfile        zipファイル名
     * @param  array  $arr_attachment 圧縮対象のファイルパスの配列    
     * @param  string $password       zipファイルのパスワード
     * @return string $zipfile        zipファイル名
     */
    public static function createZip( $zipfile=NULL, $arr_attachment=[], $password=NULL )
    {
        if ( count( $arr_attachment ) > 0 )
        {
            $zipfile = NULL === $zipfile ? config( 'product.mail_delivery.common.attachment_path' ) . time() . '.zip' : $zipfile;
            $attachment = join( ' ', $arr_attachment );
            system( 'zip -jP ' . $password . ' ' . $zipfile . ' ' . $attachment );
        }
        else
        {
            $zipfile = NULL;
        }
        return $zipfile;
    }
}
