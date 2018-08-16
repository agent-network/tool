<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSAdminUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !( Schema::hasTable( 's_admin_users' ) ) )
        {
            Schema::create( 's_admin_users', function ( Blueprint $table )
            {
                $table->increments( 'id' );                               # ID
                $table->string( 'email', 128 )->nullable();               # メールアドレス
                $table->string( 'password', 64 )->nullable();             # パスワード
                $table->string( 'last_name', 256 )->nullable();           # 姓
                $table->string( 'first_name', 256 )->nullable();          # 名
                $table->string( 'last_name_kana', 256 )->nullable();      # 姓(カナ)
                $table->string( 'first_name_kana', 256 )->nullable();     # 名(カナ)
                $table->string( 'sub_email01', 128 )->nullable();         # 送信元メールアドレス1
                $table->string( 'sub_email02', 128 )->nullable();         # 送信元メールアドレス2
                $table->tinyInteger( 'level' )->nullable();               # 権限レベル
                $table->string( 'remember_token', 64 )->nullable();       # リメンバートークン
                $table->char( 'status', 1 )->default( '1' );              # ステータス
                $table->char( 'delete_flg', 1 )->default( '0' );          # 削除フラグ
                $table->integer( 'regist_admin_user_id' )->nullable();    # 登録管理ユーザID
                $table->dateTime( 'regist_admin_user_date' )->nullable(); # 登録日時(管理ユーザ)
                $table->integer( 'update_admin_user_id' )->nullable();    # 更新管理ユーザID
                $table->dateTime( 'update_admin_user_date' )->nullable(); # 更新日持(管理ユーザ)
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 's_admin_users' );
    }
}
