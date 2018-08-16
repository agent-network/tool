<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSMailDeliveryAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !( Schema::hasTable( 's_mail_delivery_addresses' ) ) )
        {   
            Schema::create( 's_mail_delivery_addresses', function ( Blueprint $table )
            {   
                $table->increments( 'id' );                               # ID
                $table->integer( 'mail_delivery_id' )->nullable();        # メール配信ID 
                $table->string( 'email', 128 )->nullable();               # 配信先メールアドレス
                $table->char( 'open_flg', 1 )->default( '0' );            # 開封フラグ 1:した 0:していない
                $table->dateTime( 'open_date' )->nullable();              # 開封日時
                $table->char( 'status', 1 )->default( '1' );              # ステータス
                $table->char( 'delete_flg', 1 )->default( '0' );          # 削除フラグ
                $table->integer( 'regist_admin_user_id' )->nullable();    # 登録管理ユーザID 
                $table->dateTime( 'regist_admin_user_date' )->nullable(); # 登録日時(管理ユーザ)
                $table->integer( 'update_admin_user_id' )->nullable();    # 更新管理ユーザID 
                $table->dateTime( 'update_admin_user_date' )->nullable(); # 更新日持(管理ユーザ)
                
                $table->index( 'mail_delivery_id' );
                $table->index( 'email' );
                $table->index( 'open_flg' );
                $table->index( 'status' );
                $table->index( 'delete_flg' );
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
        Schema::dropIfExists( 's_mail_delivery_addresses' );
    }
}
