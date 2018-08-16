<?php

namespace App\Http\Controllers\Web\Mail;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as CommonController;

use App\Eloquents\Mysql\SMailDelivery;

use App\Utilities\CommonUtility;
use App\Utilities\MailUtility;
use App\Utilities\FileUtility;

class IndexController extends CommonController
{
    private static $sess_index_key  = 'mail_index_search';

    private static $arr_mail = [];
    private static $arr_error = [];
    private static $arr_year = [];
    private static $arr_month = [];
    private static $arr_day = [];
    private static $arr_hour = [];
    private static $arr_minute = [];

    /**
     * コンストラクタ
     *
     * @param void
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        self::$arr_year       = CommonUtility::getYear();
        self::$arr_month      = CommonUtility::getMonth();
        self::$arr_day        = CommonUtility::getDay();
        self::$arr_hour       = CommonUtility::getHour();
        self::$arr_minute     = CommonUtility::getMinute();
    }


    /**
     * メール配信履歴
     *
     * @param  Request $request 
     * @return mixed   $data
     */
    public function index( Request $request, $page=1 )
    {
        // ログインチェック
        self::isNotLoginRedirect();
        // パラメータ設定
        self::setParamater( $request, 'メール配信管理', 'メール配信履歴',
                            config( 'product.page.menu_id.mail_delivery' ), config( 'product.auth.level.normal.id' ) );

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
                return redirect( 'mail' );
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

        // システム権限でない場合は、自分が登録したものだけを抽出する条件を追加
        if ( config( 'product.auth.level.system.id' ) != self::$setting['user']['level'] )
        {
            $arr_search['regist_admin_user_id'] = self::$setting['user']['id'];
        }

        $arr_mail = SMailDelivery::getListData( $page, $arr_search );

        return view( 'mail_delivery/index',
                     [
                         'setting'     => self::$setting,
                         'data'        => $arr_mail,
                         'search'      => $arr_search,
                         'time'        => time(),
                     ]
                   );
    }


    /**
     * 配信メール詳細
     *
     * @param  Request $request
     * @return mixed   $data
     */
    public function detail( Request $request, $mail_delivery_id=NULL )
    {
        // ログインチェック
        self::isNotLoginRedirect();
        // パラメータ設定
        self::setParamater( $request, 'メール配信管理', 'メール配信設定情報',
                            config( 'product.page.menu_id.mail_delivery' ), config( 'product.auth.level.normal.id' ) );

        $template = 'mail_delivery/detail';

        // 対象ユーザ情報の取得
        self::getData( $mail_delivery_id );
        if ( empty( self::$arr_mail ) )
        {
            $template = 'mail_delivery/error';
        }

        $mode = $request->input( 'mode' );
        $arr_post = self::convertData( $request->all() );

        switch ( $mode )
        {
            case 'test_delivery':
                self::sendTestMail();
                break;
            case 'complete':
                self::changeDeliveryStatus( config( 'product.mail_delivery.delivery_status.wait.id' ) );
                self::$arr_mail['status'] = config( 'product.mail_delivery.delivery_status.wait.id' );
                break;
            case 'cancel':
                self::changeDeliveryStatus( config( 'product.mail_delivery.delivery_status.cancel.id' ) );
                self::$arr_mail['status'] = config( 'product.mail_delivery.delivery_status.cancel.id' );
                break;
            case 'delete':
                self::deleteDataById( $mail_delivery_id );
                return redirect( 'mail' );
                break;
            default:
                break;
        }

        return view( $template,
                     [
                         'setting' => self::$setting,
                         'data'    => self::$arr_mail,
                         'years'   => self::$arr_year,
                         'months'  => self::$arr_month,
                         'days'    => self::$arr_day,
                         'hours'   => self::$arr_hour,
                         'minutes' => self::$arr_minute,
                         'time'    => time(),
                     ]
                   );
    }


    /**
     * メール配信設定
     *
     * @param  Request $request
     * @return mixed   $data
     */
    public function edit( Request $request, $mail_delivery_id=NULL )
    {
        // ログインチェック
        self::isNotLoginRedirect();
        // パラメータ設定
        self::setParamater( $request, 'メール配信管理', 'メール配信設定',
                            config( 'product.page.menu_id.mail_delivery' ), config( 'product.auth.level.normal.id' ) );

        $template = 'mail_delivery/edit';

        $mode = $request->input( 'mode' );
        $arr_post = self::convertData( $request->all() );
//        $arr_post['attachment'] = $request->file( 'attachment' );
//        $arr_post['csv_file'] = $request->file( 'csv_file' );

        switch ( $mode )
        {
            case 'commit':
                // 添付ファイルアップロード処理
                $arr_post = self::uploadFile( $arr_post );
                // 配信先CSVファイルアップロード処理
                $arr_post = self::uploadCsvFile( $arr_post );
                // 添付ファイル削除処理
                $arr_post = self::deleteFile( $arr_post );
                // バリデーション
                $result = self::validatorInputData( $arr_post );

                if ( $result )
                {
                    $new_mail_delivery_id = self::saveData( $arr_post, $mail_delivery_id );
                    return redirect( '/mail/detail/' . $new_mail_delivery_id );
                }
                break;
            default:
                if ( NULL !== $mail_delivery_id )
                {
                    self::getData( $mail_delivery_id );
                    $arr_post = self::$arr_mail;
                    if ( empty( $arr_post ) )
                    {
                        $template = 'user/basis/error';
                    }
                }
                else
                {
                    $arr_post['from_email']    = config( 'product.mail.common.from.address' );
                    $arr_post['delivery_type'] = config( 'product.mail_delivery.delivery_type.text.id' );
                    $arr_post['reserve_flg']   = config( 'product.mail_delivery.reserve_flg.off.id' );
                }
                break;
        }

        return view( $template,
                     [
                         'setting'          => self::$setting,
                         'mail_delivery_id' => $mail_delivery_id,
                         'data'             => $arr_post,
                         'errors'           => self::$arr_error,
                         'years'            => self::$arr_year,
                         'months'           => self::$arr_month,
                         'days'             => self::$arr_day,
                         'hours'            => self::$arr_hour,
                         'minutes'          => self::$arr_minute,
                         'time'             => time(),
                     ]
                   );
    }


    /**
     * メール設定情報の取得
     *
     * @param  integer $mail_delivery_id メール配信ID
     * @return void
     */
    private static function getData( $mail_delivery_id=NULL )
    {
        self::$arr_mail = SMailDelivery::getTargetData( $mail_delivery_id );
    }


    /**
     * メール配信履歴の削除
     *
     * @param  array $arr_post メール配信IDの配列
     * @return void
     */
    private static function deleteData( $arr_post=[] )
    {
        if ( isset( $arr_post['mail_delivery_ids'] ) )
        {
            foreach ( $arr_post['mail_delivery_ids'] as $mail_delivery_id )
            {
                self::deleteDataById( $mail_delivery_id );
            }
        }
    }


    /**
     * メール配信履歴の削除(IDベース)
     *
     * @param  array $arr_mail_delivery_id メール配信IDの配列
     * @return void
     */
    private static function deleteDataById( $mail_delivery_id=NULL )
    {
        // 添付ファイルがあった場合に削除するために情報を事前に取得
        $arr_delivery_data = SMailDelivery::getDataById( $mail_delivery_id );
        // レコード削除
        SMailDelivery::deleteDataById( $mail_delivery_id );

        // 添付用ファイルの削除
        for ( $i = 1; $i <= config( 'product.mail_delivery.common.attachment_file_number' ); $i++ )
        {
            $original_name = 'original_name' . $i;
            $attachment_file = 'attachment_file' . $i;
            if ( NULL !== $arr_delivery_data[$original_name] )
            {
                unlink( config( 'product.mail_delivery.common.attachment_path' ) . $arr_delivery_data[$attachment_file] );
            }
        }
    }


    /**
     * データ登録・更新
     *
     * @param  array   $data             対象配列データ
     * @param  integer $mail_delivery_id メール配信ID
     * @return integer $mail_delivery_id メール配信ID
     */
    private static function saveData( array $data, $mail_delivery_id=NULL )
    {
        // 設定情報の登録・更新
        $new_mail_delivery_id = SMailDelivery::saveData( $data, $mail_delivery_id );

        // 添付ファイルの保存
        if ( isset( $data['attachment_info'] ) )
        {
            foreach ( $data['attachment_info'] as $value )
            {
                if ( '' != $value['original'] )
                {
                    if ( file_exists( config( 'product.mail_delivery.common.temporary_path' ) . $value['save'] ) )
                    {
                        rename( config( 'product.mail_delivery.common.temporary_path' ) . $value['save'],
                                config( 'product.mail_delivery.common.attachment_path' ) . $value['save'] );
                    }
                }
            }
        }

        // 配信先ファイルの保存
        if ( file_exists( config( 'product.mail_delivery.common.temporary_path' ) . $data['csv_info']['save'] ) )
        {
            rename( config( 'product.mail_delivery.common.temporary_path' ) . $data['csv_info']['save'], 
                    config( 'product.mail_delivery.common.csv_path' ) . $data['csv_info']['save'] );
        }

        return $new_mail_delivery_id;
    }


    /**
     * 添付ファイルアップロード
     *
     * @param  array $data 対象配列データ
     * @return array $data 対象配列データ
     */
    private static function uploadFile( $data )
    {
        if ( isset( $data['attachment'] ) && is_array( $data['attachment'] ) )
        {
            foreach ( $data['attachment'] as $key => $value )
            {
                if ( $data['attachment'][$key]->isValid() && !isset( self::$arr_error['attachment.' . $key] ) )
                {
                    $save_name = md5( $key . microtime() ) . '.'
                               . pathinfo( $value->getClientOriginalName(), PATHINFO_EXTENSION );
                    $data['attachment_info'][$key]['original'] = $value->getClientOriginalName();
                    $data['attachment_info'][$key]['save']     = $save_name;
                    $data['attachment_info'][$key]['mime']     = $value->getMimeType();
//                    $data['attachment_info'][$key]['isimage']  = FileUtility::isImage( $value->getMimeType() );
                    $data['attachment'][$key]->move( config( 'product.mail_delivery.common.temporary_path' ), $save_name );
                }
            }
        }
        return $data;
    }


    /**
     * 添付ファイル削除
     *
     * @param  array $data 対象配列データ     
     * @return array $data 対象配列データ
     */
    private static function deleteFile( $data )
    {
        if ( isset( $data['attachment_delete'] ) )
        {
            foreach ( $data['attachment_delete'] as $key => $value )
            {
                unlink( config( 'product.mail_delivery.common.temporary_path' ) . $data['attachment_info'][$key]['save'] );
                unlink( config( 'product.mail_delivery.common.attachment_path' ) . $data['attachment_info'][$key]['save'] );
                unset( $data['attachment_info'][$key] );
            }
        }
        return $data;
    }


    /**
     * 配信先CSVファイルアップロード
     *
     * @param  array $data 対象配列データ
     * @return array $data 対象配列データ
     */
    private static function uploadCsvFile( $data )
    {
        if ( isset( $data['csv_file'] ) && !is_null( $data['csv_file'] ) )
        {
            if ( $data['csv_file']->isValid() && !isset( self::$arr_error['csv_file'] ) )
            {
                $save_name = md5( microtime() ) . '.'
                           . pathinfo( $data['csv_file']->getClientOriginalName(), PATHINFO_EXTENSION );
                $data['csv_info']['original'] = $data['csv_file']->getClientOriginalName();
                $data['csv_info']['save']     = $save_name;
                $data['csv_info']['mime']     = $data['csv_file']->getMimeType();
                $data['csv_file']->move( config( 'product.mail_delivery.common.temporary_path' ), $save_name );
            }
        }
        return $data;
    }


    /**
     * バリデーション(データチェック)
     *
     * @param  array   $data    対象配列データ
     * @param  integer $user_id ユーザID
     * @return bool
     */
    private static function validatorInputData( array $data, $user_id=NULL )
    {
        $rules = [
            'from_email'    => 'required|email',
            'title'         => 'required|max:80',
            'body'          => 'required|max:10000',
            'delivery_type' => 'required|integer',
            'reserve_flg'   => 'required|integer',
            'reserve_year'  => 'required_if:reserve_flg,' . config( 'product.mail_delivery.reserve_flg.on.id' ) . '|integer',
            'reserve_month' => 'required_if:reserve_flg,' . config( 'product.mail_delivery.reserve_flg.on.id' ) . '|integer',
            'reserve_day'   => 'required_if:reserve_flg,' . config( 'product.mail_delivery.reserve_flg.on.id' ) . '|integer|validreservedate',
            'csv_file'      => 'required|mimes:csv|max:32000',
        ];
        $messages = [
            'email.required'            => 'メールアドレスは必ず入力してください',
            'email.email'               => '正しいメールアドレスの形式で入力してください',

            'title.required'            => 'タイトルは必ず入力してください',
            'title.max'                 => 'タイトルは80文字以下で入力してください',

            'body.required'             => '本文は必ず入力してください',
            'body.max'                  => '本文は10000文字以下で入力してください',

            'delivery_type.required'    => 'メール形式は必ず選択してください',
            'delivery_type.integer'     => 'メール形式は数字で入力してください',

            'reserve_flg.required'      => '配信設定は必ず選択してください',
            'reserve_flg.integer'       => '配信設定は数字で入力してください',

            'reserve_year.required_if'     => '予約配信を選択した場合、予約配信日時(年)は必ず選択してください',
            'reserve_year.integer'         => '予約配信日時(年)は数字で入力してください',
            'reserve_month.required_if'    => '予約配信を選択した場合、予約配信日時(月)は必ず選択してください',
            'reserve_month.integer'        => '予約配信日時(月)は数字で入力してください',
            'reserve_day.required_if'      => '予約配信を選択した場合、予約配信日時(日)は必ず選択してください',
            'reserve_day.integer'          => '予約配信日時(日)は数字で入力してください',
            'reserve_day.validreservedate' => '予約配信日時は正しい日付を選択してください',

            'csv_file.required'         => '配信先CSVファイルは必ずアップロードしてください',
            'csv_file.mimes'            => '配信先CSVファイルはCSV形式でアップロードしてください',
            'csv_file.max'              => '配信先CSVファイルは32MB以下でアップロードしてください',
        ];

        if ( isset( $data['attachment'] ) && is_array( $data['attachment'] ) )
        {
            foreach ( $data['attachment'] as $key => $value )
            {
                $rules['attachment.' . $key] = 'max:32000';
                $messages['attachment.' . $key . '.max']   = '添付ファイルは32MB以下でアップロードしてください';
            }
        }

        $validator = Validator::make( $data, $rules, $messages );
        if ( $validator->fails() )
        {
            $error = json_decode( $validator->messages(), true );
            foreach ( $error as $k => $v )
            {
                self::$arr_error[$k] = $v[0];
            }
        }

        // 配信先CSVファイルの内容の最低限のフォーマットチェック
        if ( !isset( self::$arr_error['csv_file'] ) )
        {
            $test_flg   = 0;
            $status_flg = 0;
            $to_flg     = 0;
            $arr_csv = FileUtility::parseCsv( config( 'product.mail_delivery.common.temporary_path' )
                                            . $data['csv_info']['save'] );
            foreach ( $arr_csv[0] as $value )
            {
                if( preg_match( '/^test$/', $value ) )
                {
                    $test_flg++;
                }
                elseif( preg_match( '/^status$/', $value ) )
                {
                    $status_flg++;
                }
                elseif( preg_match( '/^to/', $value ) )
                {
                    $to_flg++;
                }
            }
            if ( 0 == $test_flg )
            {
                self::$arr_error['csv_file'] = '配信先CSVファイルにテスト配信用のカラムが用意されていません';
            }
            if ( 0 == $status_flg )
            {
                self::$arr_error['csv_file'] = '配信先CSVファイルに配信ステータスのカラムが用意されていません';
            }
            if ( 0 == $to_flg )
            {
                self::$arr_error['csv_file'] = '配信先CSVファイルに配信先アドレスのカラムが用意されていません';
            }
        }

        // 既にCSVファイルをアップロードしてある状態で、次のsubmit時に何もしなかった場合にエラーにしない処理
        if ( isset( self::$arr_error['csv_file'] ) && self::$arr_error['csv_file'] == $messages['csv_file.required'] )
        {
            if ( isset( $data['csv_info'] ) )
            {
                unset( self::$arr_error['csv_file'] );
            }
        }

        return empty( self::$arr_error ) ? TRUE : FALSE;
    }


    /**
     * テストメール配信
     *
     * @param  void 
     * @return void
     */
    private static function sendTestMail()
    {
        // メール送信
        MailUtility::makeMail( self::$arr_mail, TRUE );
    }


    /**
     * 配信ステータスの更新
     *
     * @param  integer $status ステータス
     * @return void
     */
    private static function changeDeliveryStatus( $status=NULL )
    {
        SMailDelivery::changeStatus( $status, self::$arr_mail['id'] );
    }
}
