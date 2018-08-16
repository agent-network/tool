<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSBounceMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !( Schema::hasTable( 's_bounce_mails' ) ) )
        {
            Schema::create( 's_bounce_mails', function ( Blueprint $table )
            {
                $table->string( 'server', 16 )->nullable();               # サーバ名
                $table->string( 'log_date', 8 )->nullable();              # ログファイル識別
                $table->string( 'que', 16 )->nullable();                  # キュー
                $table->string( 'date', 32 )->nullable();                 # 送信日時
                $table->string( 'from', 128 )->nullable();                # 送信元アドレス
                $table->string( 'email', 128 )->nullable();               # 配信先アドレス
                $table->string( 'status', 16 )->nullable();               # ステータス
                $table->text( 'description' )->nullable();                # 配信失敗理由
                $table->text( 'message' )->nullable();                    # ログ
                $table->integer( 'regist_admin_user_id' )->nullable();    # 作成管理ユーザID
                $table->dateTime( 'regist_admin_user_date' )->nullable(); # 作成日時
                $table->integer( 'update_admin_user_id' )->nullable();    # 更新管理ユーザID
                $table->dateTime( 'update_admin_user_date' )->nullable(); # 更新日時

                $table->index( 'server' );
                $table->index( 'log_date' );
                $table->index( 'from' );
                $table->index( 'email' );
                $table->index( 'status' );
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
        Schema::dropIfExists( 's_bounce_mails' );
    }
}
