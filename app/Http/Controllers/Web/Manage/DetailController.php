<?php

namespace App\Http\Controllers\Web\Manage;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as CommonController;

use App\Eloquents\Mysql\SAdminUser;

use App\Utilities\CommonUtility;
use App\Utilities\MailUtility;

class DetailController extends CommonController
{
    private static $arr_user = [];
    private static $arr_error = [];

    /**
     * コンストラクタ
     *
     * @param  void
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * ユーザ詳細
     *
     * @param  Request $request 
     * @param  integer $admin_user_id 
     * @return mixed   $data
     */
    public function index( Request $request, $admin_user_id=NULL )
    {
        // ログインチェック
        self::isNotLoginRedirect();
        // パラメータ設定
        self::setParamater( $request, '管理者管理', '管理者情報',
                            config( 'product.page.menu_id.manage' ), config( 'product.auth.level.system.id' ) );

        $template = 'manage/detail/index';

        $onload = NULL;
        $mode = $request->input( 'mode' );
        $arr_post = self::convertData( $request->all() );

        switch ( $mode )
        {
            case 'password':
                self::setPassword( $admin_user_id, TRUE );
                $onload = "alert( 'パスワードを再発行し、対象者へメールしました。' );";
                break;
            case 'delete':
                self::deleteData( $admin_user_id );
                return redirect( 'manage' );
                break;
            default:
                break;
        }

        // 対象ユーザ情報の取得
        self::getData( $admin_user_id );
        if ( empty( self::$arr_user ) )
        {
            $template = 'manage/detail/error';
        }

        return view( $template,
                     [
                         'setting' => self::$setting,
                         'onload'  => $onload,
                         'data'    => self::$arr_user,
                         'time'    => time(),
                     ]
                   );
    }


    /**
     * 情報編集
     *
     * @param  Request $request
     * @param  integer $admin_user_id
     * @return mixed   $data
     */
    public function edit( Request $request, $admin_user_id=NULL )
    {
        // ログインチェック
        self::isNotLoginRedirect();
        // パラメータ設定
        self::setParamater( $request, '管理者管理', '管理者情報',
                            config( 'product.page.menu_id.manage' ), config( 'product.auth.level.system.id' ) );

        $template = 'manage/detail/edit';

        $mode = $request->input( 'mode' );
        $arr_post = self::convertData( $request->all() );
        switch ( $mode )
        {
            case 'new':
            case 'edit':
                // バリデーション
                if ( self::validatorInputData( $arr_post, $admin_user_id ) )
                {
                    $new_admin_user_id = self::saveData( $arr_post, $admin_user_id );
                    if ( NULL === $admin_user_id )
                    {
                        self::setPassword( $new_admin_user_id );
                    }
                    return redirect( '/manage/detail/' . $new_admin_user_id );
                }
                break;
            default:
                if ( NULL !== $admin_user_id )
                {
                    self::getData( $admin_user_id );
                    $arr_post = self::$arr_user;
                    if ( empty( $arr_post ) )
                    {
                        $template = 'manage/detail/error';
                    }
                }
                else
                {
                    $arr_post['level'] = config( 'product.auth.all' );
                }
                break;
        }

        return view( $template,
                     [
                         'setting'       => self::$setting,
                         'admin_user_id' => $admin_user_id,
                         'data'          => $arr_post,
                         'errors'        => self::$arr_error,
                         'time'          => time(),
                     ]
                   );
    }


    /**
     * ユーザ情報の取得
     *
     * @param  integer $admin_user_id ユーザID
     * @return void
     */
    private static function getData( $admin_user_id=NULL )
    {
        self::$arr_user = SAdminUser::getDataById( $admin_user_id );
    }


    /**
     * ユーザ削除
     *
     * @param  integer $admin_user_id ユーザID
     * @return void
     */
    private static function deleteData( $admin_user_id=NULL )
    {
        SAdminUser::deleteDataById( $admin_user_id );
    }


    /**
     * データ登録・更新
     *
     * @param  array   $data          対象配列データ
     * @param  integer $admin_user_id ユーザID
     * @return integer $admin_user_id ユーザID
     */
    private static function saveData( array $data, $admin_user_id=NULL )
    {
        return SAdminUser::saveData( $data, $admin_user_id );
    }


    /**
     * パスワード設定
     *
     * @param  array   $data          対象配列データ
     * @param  integer $admin_user_id ユーザID
     * @return integer $admin_user_id ユーザID
     */
    private static function setPassword( $admin_user_id=NULL, $reset_flg=FALSE )
    {
        $password = CommonUtility::getRandomString( config( 'product.common.password.length' ) );
        SAdminUser::setPassword( $password, $admin_user_id );

        // メール送信
        self::getData( $admin_user_id );
        self::$arr_user['password'] = $password;
        self::$arr_user['delivery_type'] = config( 'product.mail_delivery.delivery_type.html.id' );
        if ( $reset_flg )
        {
            $subject  = config( 'product.mail.manage.password.subject' );
            $template = config( 'product.mail.manage.password.template' );
        } 
        else
        {
            $subject  = config( 'product.mail.manage.new.subject' );
            $template = config( 'product.mail.manage.new.template' );
        } 
        MailUtility::sendView(
            $subject,
            $template,
            self::$arr_user
        );
    }


    /**
     * バリデーション(データチェック)
     *
     * @param  array   $data          対象配列データ
     * @param  integer $admin_user_id ユーザID
     * @return bool
     */
    private static function validatorInputData( array $data, $admin_user_id=NULL )
    {
        $rules = [
            'email'           => 'required|email|max:100|unique:s_admin_users,email,' . $admin_user_id . ',id,delete_flg,0',
            'last_name'       => 'required|max:40',
            'first_name'      => 'required|max:40',
            'last_name_kana'  => 'required|max:40|katakana',
            'first_name_kana' => 'required|max:40|katakana',
            'sub_email01'     => 'email|max:100',
            'sub_email02'     => 'email|max:100',
            'level'           => 'required|integer',
        ];
        $messages = [
            'email.required'           => 'メールアドレスは必ず入力してください',
            'email.unique'             => 'このメールアドレスは既に登録されています',
            'email.email'              => '正しいメールアドレスの形式で入力してください',
            'email.max'                => 'メールアドレスは100文字以下で入力してください',

            'last_name.required'       => '氏名(姓)は必ず入力してください',
            'last_name.max'            => '氏名(姓)は40文字以下で入力してください',

            'first_name.required'      => '氏名(名)は必ず入力してください',
            'first_name.max'           => '氏名(名)は40文字以下で入力してください',

            'last_name_kana.required'  => '氏名(姓)(カナ)は必ず入力してください',
            'last_name_kana.max'       => '氏名(姓)(カナ)は40文字以下で入力してください',
            'last_name_kana.katakana'  => '氏名(姓)(カナ)は全角カナで入力してください',

            'first_name_kana.required' => '氏名(名)(カナ)は必ず入力してください',
            'first_name_kana.max'      => '氏名(名)(カナ)は40文字以下で入力してください',
            'first_name_kana.katakana' => '氏名(名)(カナ)は全角カナで入力してください',

            'sub_email01.email'        => '正しいメールアドレスの形式で入力してください',
            'sub_email01.max'          => 'メールアドレスは100文字以下で入力してください',
            'sub_email02.email'        => '正しいメールアドレスの形式で入力してください',
            'sub_email02.max'          => 'メールアドレスは100文字以下で入力してください',

            'level.required'           => '権限は必ず選択してください',
            'level.integer'            => '権限は数字で入力してください',
        ];

        $validator = Validator::make( $data, $rules, $messages );
        if ( $validator->fails() )
        {
            $error = json_decode( $validator->messages(), true );
            foreach ( $error as $k => $v )
            {
                self::$arr_error[$k] = $v[0];
            }
            return FALSE;
        }

        return TRUE;
    }
}
