<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSMailDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !( Schema::hasTable( 's_mail_deliveries' ) ) )
        {
            Schema::create( 's_mail_deliveries', function ( Blueprint $table )
            {
                $table->increments( 'id' );                               # ID
                $table->char( 'delivery_type', 1 )->default( '1' );       # メール形式 1:テキスト 2:HTML
                $table->char( 'reserve_flg', 1 )->default( '2' );         # 配信設定フラグ 1:する 2:即時配信
                $table->dateTime( 'reserve_date' )->nullable();           # 予約日時
                $table->string( 'title', 256 )->nullable();               # タイトル
                $table->text( 'body' )->nullable();                       # 本文
                $table->string( 'from_email', 128 )->nullable();          # 配信元メールアドレス
                $table->string( 'original_name1', 128 )->nullable();      # オリジナルファイル名1
                $table->string( 'attachment_file1', 128 )->nullable();    # 添付ファイル名1
                $table->string( 'original_name2', 128 )->nullable();      # オリジナルファイル名2
                $table->string( 'attachment_file2', 128 )->nullable();    # 添付ファイル名2
                $table->string( 'original_name3', 128 )->nullable();      # オリジナルファイル名3
                $table->string( 'attachment_file3', 128 )->nullable();    # 添付ファイル名3
                $table->string( 'original_csv_name', 128 )->nullable();   # 配信先CSVオリジナルファイル名
                $table->string( 'delivery_csv_file', 128 )->nullable();   # 配信先CSVファイル名
                $table->dateTime( 'delivery_date' )->nullable();          # 配信完了日時
                $table->char( 'status', 1 )->default( '0' );              # ステータス 0:配信準備中 1:配信待ち 2:配信済 3:キャンセル
                $table->char( 'delete_flg', 1 )->default( '0' );          # 削除フラグ
                $table->integer( 'regist_admin_user_id' )->nullable();    # 登録管理ユーザID
                $table->dateTime( 'regist_admin_user_date' )->nullable(); # 登録日時(管理ユーザ)
                $table->integer( 'update_admin_user_id' )->nullable();    # 更新管理ユーザID
                $table->dateTime( 'update_admin_user_date' )->nullable(); # 更新日持(管理ユーザ)

                $table->index( 'delivery_type' );
                $table->index( 'reserve_flg' );
                $table->index( 'reserve_date' );
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
        Schema::dropIfExists( 's_mail_deliveries' );
    }
}
