<?php

namespace App\Service;
use Illuminate\Support\Facades\Auth;
use App\Model\Region;
use Illuminate\Support\Facades\DB;

//计算司机推送频道名称
class DriverChannel
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
        $origin_code = $this->getAddress($this->driverRoute->origin_longitude,$this->driverRoute->origin_latitude);
        $destination_code = $this->getAddress($this->driverRoute->destination_longitude, $this->driverRoute->destination_latitude);
        //Redis key设计,当起点和终点都与用户的同区的话,就推送
        $base_key = "driver:origin:$origin_code:destination:$destination_code";
        $start_time = $this->getHour($this->driverRoute->depart_time);
        $end_time = $this->getHour($this->driverRoute->arrive_time);
        $key = $base_key . ':str:' . $start_time . ':end:' . $end_time;

        $this->driverRoute->redis_key = $key;
        $this->driverRoute->save();

        return $key;
    }


    private function getAddress($longitude, $latitude)
    {
        $ak = env('BAIDU_MAP_AK');
        $baseUrl = "http://api.map.baidu.com/geocoder/v2/?location=$latitude,$longitude&coordtype=bd09ll&output=json&pois=1&latest_admin=1&ak=$ak";
        $data = $this->geturl($baseUrl);

        $city = $data['result']['addressComponent']['city'];
        $district = $data['result']['addressComponent']['district'];
        
        //查找出对应district的6位所属regionID
        $res = Db::select("select b.REGION_CODE from region a, region b where a.REGION_NAME = :parent_name and b.REGION_NAME = :region_name",[':parent_name' => $city, ':region_name' => $district]);
        $code = $res[0]->REGION_CODE;

        return $code;
    }

    private function geturl($url){
        $headerArray =array("Content-type:application/json;","Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output,true);
        return $output;
    }


    private function getHour($time){
        return substr($time,0,2);
    }

}
