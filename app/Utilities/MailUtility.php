<?php

namespace App\Utilities;

use Mail;

use App\Eloquents\Mysql\SMailDeliveryAddress;

class MailUtility
{
    public static function sendView( $subject, $template, $data=[] )
    {
        Mail::send(
            $template,
            $data,
            function ( $message ) use ( $data, $subject )
            {
                $message->subject( config( 'product.mail.common.subject.prefix' ) . $subject );
                $message->from( config( 'product.mail.common.from.address' ), config( 'product.mail.common.from.nickname' ) );
                if ( isset( $data['email'] ) )
                {
                    if ( isset( $data['last_name'] ) && isset( $data['first_name'] ) )
                    {
                        $message->to( $data['email'], $data['last_name'] . ' ' . $data['first_name'] . ' 様' );
                    }
                    else
                    {
                        $message->to( $data['email'] );
                    }
                }
                // TOの設定
                if ( isset( $data['to'] ) )
                {
                    foreach ( $data['to'] as $value )
                    {
                        if ( empty( $value ) )
                        {
                            continue;
                        }
                        $message->to( $value );
                    }
                }
                // CCが指定された場合
                if ( isset( $data['cc'] ) )
                {
                    foreach ( $data['cc'] as $value )
                    {   
                        if ( empty( $value ) )
                        {
                            continue;
                        }
                        $message->cc( $value );
                    }
                }
                // BCCが指定された場合
                if ( isset( $data['bcc'] ) )
                {
                    foreach ( $data['bcc'] as $value )
                    {
                        if ( empty( $value ) )
                        {
                            continue;
                        }
                        $message->bcc( $value );
                    }
                }
//                $message->bcc( config( 'product.mail.common.bcc.address01' ) );

                $swiftMessage = $message->getSwiftMessage();

                // HTML形式メール指定の場合
                if ( config( 'product.mail_delivery.delivery_type.html.id' ) == $data['delivery_type'] )
                {   
                    $swiftMessage->setContentType( 'text/html' );
                }
                else
                {
                    $swiftMessage->setContentType( 'text/plain' );
                }

                // 添付ファイルがある場合
                if ( !empty( $data['attachment'] ) )
                {   
                    $swiftMessage->setBody( NULL );
                    if ( config( 'product.mail_delivery.delivery_type.html.id' ) == $data['delivery_type'] )
                    {   
                        $swiftMessage->addPart( $data['body'], 'text/html' );
                    }
                    else
                    {   
                        $swiftMessage->addPart( $data['body'], 'text/plain' );
                    }
                    $message->attach( $data['attachment'], [ 'as' => 'attachments.zip' ] );
                }
            }
        );
    }


    public static function sendRaw( $data=[] )
    {
        Mail::raw(
            $data['body'],
            function ( $message ) use ( $data )
            {
                $message->subject( config( 'product.mail.common.subject.prefix' ) . $data['title'] );
                $message->from( $data['from'] );
                // TOの設定
                foreach ( $data['to'] as $value )
                {
                    if ( empty( $value ) )
                    {
                        continue;
                    }
                    $message->to( $value );
                }
                // CCが指定された場合
                if ( isset( $data['cc'] ) )
                {
                    foreach ( $data['cc'] as $value )
                    {
                        if ( empty( $value ) )
                        {
                            continue;
                        }
                        $message->cc( $value );
                    }
                }
                // BCCが指定された場合
                if ( isset( $data['bcc'] ) )
                {
                    foreach ( $data['bcc'] as $value )
                    {
                        if ( empty( $value ) )
                        {
                            continue;
                        }
                        $message->bcc( $value );
                    }
                }
//                $message->bcc( config( 'product.mail.bcc.address01' ) );

                $swiftMessage = $message->getSwiftMessage();

                // HTML形式メール指定の場合
                if ( config( 'product.mail_delivery.delivery_type.html.id' ) == $data['delivery_type'] )
                {
                    $swiftMessage->setContentType( 'text/html' );
                }
                else
                {
                    $swiftMessage->setContentType( 'text/plain' );
                }

                // 添付ファイルがある場合
                if ( !empty( $data['attachment'] ) )
                {
                    $swiftMessage->setBody( NULL );
                    if ( config( 'product.mail_delivery.delivery_type.html.id' ) == $data['delivery_type'] )
                    {
                        $swiftMessage->addPart( $data['body'], 'text/html' );
                    }
                    else
                    {
                        $swiftMessage->addPart( $data['body'], 'text/plain' );
                    }
                    $message->attach( $data['attachment'], [ 'as' => 'attachments.zip' ] );
                }
            }
        );
    }


    public static function changeFormatCsv( $arr_csv_data=[], $test_delivery_flg=FALSE )
    {
        $i = 0;
        $arr_data = [];
        foreach ( $arr_csv_data as $count => $data )
        {
            if ( $test_delivery_flg )
            {
                if ( isset( $data['test'] ) && config( 'product.common.flag.on' ) == $data['test'] )
                {
                    $arr_data[$i] = $data;
                    foreach ( $data as $key => $value )
                    {   
                        if ( preg_match( '/^to/', $key ) )
                        {   
                            $arr_data[$i]['to'][] = $value;
                        }
                        else if ( preg_match( '/^cc/', $key ) )
                        {   
                            $arr_data[$i]['cc'][] = $value;
                        }
                        else if ( preg_match( '/^bcc/', $key ) )
                        {   
                            $arr_data[$i]['bcc'][] = $value;
                        }
                    }
                    $i++;
                }
            }
            else
            {
                $arr_data[$count] = $data;
                foreach ( $data as $key => $value )
                {
                    if ( preg_match( '/^to/', $key ) )
                    {
                        $arr_data[$count]['to'][] = $value;
                    }
                    else if ( preg_match( '/^cc/', $key ) )
                    {
                        $arr_data[$count]['cc'][] = $value;
                    }
                    else if ( preg_match( '/^bcc/', $key ) )
                    {
                        $arr_data[$count]['bcc'][] = $value;
                    }
                }
            }
        }

        return $arr_data;
    }


    /**
     * メールデータの形成及びメール送信メソッド呼び出し
     *
     * @param  array $arr_delivery_data メール配信データ
     * @return void
     */
    public static function makeMail( $arr_delivery_data=[], $test_delivery_flg=FALSE )
    {
        // 添付ファイルの圧縮パスワード
        $password = CommonUtility::getRandomString( config( 'product.common.password.length' ) );
        // 添付ファイルの生成
        $zipfile = self::makeAttachmentFile( $password, $arr_delivery_data );
        // 配信先CSVファイルを配列に変換
        $arr_csv_data = self::changeFormatCsv( FileUtility::parseCsv( config( 'product.mail_delivery.common.csv_path' )
                                                                      . $arr_delivery_data['delivery_csv_file'], TRUE ),
                                               $test_delivery_flg );

        foreach ( $arr_csv_data as $line )
        {
            $arr_send = [];
            $arr_send['delivery_type'] = $arr_delivery_data['delivery_type'];
            $arr_send['from']          = $arr_delivery_data['from_email'];
            $arr_send['title']         = $arr_delivery_data['title'];
            $arr_send['body']          = $arr_delivery_data['body'];
            foreach ( $line as $key => $value )
            {   
                if ( 'to' == $key || 'cc' == $key || 'bcc' == $key )
                {   
                    $arr_send[$key] = $value;
                }
                else
                {
                    // 開封確認タグを挿入
                    if ( !$test_delivery_flg )
                    {
                        if ( !empty( $value ) && 'to1' == $key )
                        {
                            $line['send_flg'] = config( 'product.common.flag.on' );
                            $address_id = SMailDeliveryAddress::saveData( $arr_delivery_data['id'], $line );
                            if ( config( 'product.mail_delivery.delivery_type.html.id' ) == $arr_delivery_data['delivery_type'] )
                            {
                                $tag = preg_replace( '/{{id}}/', $address_id, config( 'product.mail_delivery.affiliate.tag' ) );
                                $arr_send['body'] .= $tag;
                            }
                        }
                    }
                    $value = preg_replace( '/\\\"/', '"', $value );
                    $value = preg_replace( '/\\\,/', ',', $value );
                    $match = '/{{' . $key . '}}/';
                    $arr_send['title'] = preg_replace( $match, $value, $arr_send['title'] );
                    $arr_send['body'] = preg_replace( $match, $value, $arr_send['body'] );
                }
            }
            $arr_send['body'] = nl2br( $arr_send['body'], FALSE );
            $arr_send['attachment'] = $zipfile;

            self::sendRaw( $arr_send );

            // 添付ファイルがある場合、別途パスワードを送るメールを送信
            if ( !is_null( $zipfile ) )
            {
                self::sendPasswordMail( $password, $arr_send );
            }
        }

        // 送信後処理
        self::closeProcess( $arr_delivery_data, $zipfile );
    }


    /**
     * 添付ファイルが存在した場合にパスワードメールを送信する
     *
     * @param  string $password パスワード
     * @param  array  $arr_send メール配信データ
     * @return void
     */
    public static function sendPasswordMail( $password=NULL, $arr_send=[] )
    {
        $arr_send['delivery_type'] = config( 'product.mail_delivery.delivery_type.text.id' );
        $arr_send['password'] = $password;
        unset( $arr_send['attachment'] );
        self::sendView( config( 'product.mail.zipfile.password.subject' ),
                        config( 'product.mail.zipfile.password.template' ), $arr_send );
    }


    /**
     * 添付ファイルがある場合元のファイル名に戻し、パスワード付きzipファイルを生成する
     *
     * @param  string $password          パスワード
     * @param  array  $arr_delivery_data メール配信データ
     * @return string $zipfile           zipファイル名(パス付)
     */
    public static function makeAttachmentFile( $password=NULL, $arr_delivery_data=[] )
    {
        $arr_attachment = [];
        for ( $i = 1; $i <= config( 'product.mail_delivery.common.attachment_file_number' ); $i++ )
        {
            $original_name = 'original_name' . $i;
            $attachment_file = 'attachment_file' . $i;
            if ( NULL !== $arr_delivery_data[$original_name] )
            {
                copy( config( 'product.mail_delivery.common.attachment_path' ) . $arr_delivery_data[$attachment_file],
                      config( 'product.mail_delivery.common.temporary_path' ) . $arr_delivery_data[$original_name] );
                $arr_attachment[] = config( 'product.mail_delivery.common.temporary_path' )
                                  . preg_replace( '/ /', '\ ', $arr_delivery_data[$original_name] );
            }
        }

        return FileUtility::createZip( NULL, $arr_attachment, $password );
    }


    /**
     * メール送信後の後処理
     *
     * @param  string $password パスワード
     * @param  string $zipfile  zipファイル名(パス付)
     * @return void
     */
    public static function closeProcess( $arr_delivery_data=[], $zipfile=NULL )
    {
        for ( $i = 1; $i <= config( 'product.mail_delivery.common.attachment_file_number' ); $i++ )
        {
            $original_name = 'original_name' . $i;
            if ( !is_null( $arr_delivery_data[$original_name] ) )
            {
                unlink( config( 'product.mail_delivery.common.temporary_path' ) . $arr_delivery_data[$original_name] );
            }
        }
        if ( !is_null( $zipfile ) )
        {
            unlink( $zipfile );
        }
    }
}
