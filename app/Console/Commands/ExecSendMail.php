<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;

use App\Eloquents\Mysql\SMailDelivery;
use App\Eloquents\Mysql\SMailDeliveryAddress;

use App\Utilities\MailUtility;

class ExecSendMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exec_send_mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 即時配信
        $this->info( '即時配信処理を開始します. date:' . Carbon::now() );
        self::execSendMailInstantly();
        $this->info( '即時配信処理を終了します. date:' . Carbon::now() );

        // 予約配信
        $this->info( '予約配信処理を開始します. date:' . Carbon::now() );
        self::execSendMailBook();
        $this->info( '予約配信処理を終了します. date:' . Carbon::now() );
    }


    private function execSendMailInstantly()
    {
        $ojb_mail_delivery = SMailDelivery::where( 'status', '=', config( 'product.common.flag.on' ) )
                                          ->where( 'delete_flg', '=', config( 'product.common.flag.off' ) )
                                          ->where( 'reserve_flg', '=', config( 'product.mail_delivery.reserve_flg.off.id' ) )
                                          ->select( 'id' )
                                          ->get();

        $this->info( '対象配信データ件数[' . count( $ojb_mail_delivery ) . ']件です.' );

        if ( is_object( $ojb_mail_delivery ) )
        {
            // ステータスの更新
            SMailDelivery::where( 'status', '=', config( 'product.common.flag.on' ) )
                         ->where( 'delete_flg', '=', config( 'product.common.flag.off' ) )
                         ->where( 'reserve_flg', '=', config( 'product.mail_delivery.reserve_flg.off.id' ) )
                         ->update([
                             'status' => config( 'product.mail_delivery.delivery_status.progress.id' )
                         ]);
            // メール送信
            self::processMail( $ojb_mail_delivery );
        }
    }


    private function execSendMailBook()
    {
        $ojb_mail_delivery = SMailDelivery::where( 'status', '=', config( 'product.common.flag.on' ) )
                                          ->where( 'delete_flg', '=', config( 'product.common.flag.off' ) )
                                          ->where( 'reserve_flg', '=', config( 'product.mail_delivery.reserve_flg.on.id' ) )
                                          ->where( 'reserve_date', '<=', Carbon::now() )
                                          ->select( 'id' )
                                          ->get();

        $this->info( '対象配信データ件数[' . count( $ojb_mail_delivery ) . ']件です.' );

        if ( is_object( $ojb_mail_delivery ) )
        {
            // ステータスの更新
            SMailDelivery::where( 'status', '=', config( 'product.common.flag.on' ) )
                         ->where( 'delete_flg', '=', config( 'product.common.flag.off' ) )
                         ->where( 'reserve_flg', '=', config( 'product.mail_delivery.reserve_flg.off.id' ) )
                         ->where( 'reserve_date', '<=', Carbon::now() )
                         ->update([
                             'status' => config( 'product.mail_delivery.delivery_status.progress.id' )
                         ]);
            // メール送信
            self::processMail( $ojb_mail_delivery );
        }
    }


    private function processMail( $ojb_mail_delivery )
    {
        foreach ( $ojb_mail_delivery as $data )
        {
            $arr_delivery_data = SMailDelivery::getTargetData( $data['id'] );
            MailUtility::makeMail( $arr_delivery_data );

            // ステータスの更新
            SMailDelivery::where( 'id', '=', $data['id'] )
                         ->update([
                             'delivery_date' => Carbon::now(),
                             'status' => config( 'product.mail_delivery.delivery_status.ok.id' )
                         ]);
        }
    }
}
