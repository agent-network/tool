<?php

namespace App\Http\Controllers\Web\Mail;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as CommonController;

use App\Eloquents\Mysql\SMailDeliveryAddress;

class OpenController extends CommonController
{
    /**
     * コンストラクタ
     *
     * @param void
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * メール開封設定
     *
     * @param  Request $request 
     * @return void
     */
    public function index( Request $request, $id )
    {
        SMailDeliveryAddress::setOpenFlg( $id );
    }
}
