<?php

namespace App\Service;
use Illuminate\Support\Facades\Auth;
use App\Model\Region;
use Illuminate\Support\Facades\DB;

//司机下单服务类
class DriverPlaceOrder
{
    protected $driverRoute;
    protected $isUpdate;

    //最大尝试次数
    public $tries = 1;
    public function __construct($driverRoute,$isUpdate = false)
    {
        $this->driverRoute = $driverRoute;
        $this->isUpdate = $isUpdate;
    }

   
    public function handle()
    {

       
    }


   


}
