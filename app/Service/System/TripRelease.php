<?php

namespace App\Service\System;

use App\Model\Travel\PublishTrip;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Model\Travel\DriverRoute;

//行程发布的一些异步操作
/**
 * 计算出路线Key
 * 3. 计算出路线Key
 * 4. 根据Key获取到司机路线
 * 5. 计算司机的出行路线，计算自己的出行路线
 * 6. 出行路线比较，重复率大于70%，则匹配
 * 7. 把行程ID trip_id推送到司机队列中,推送频道名称为计算出的Key
 * 8. 行程ID放入Redis集合中
 */

class TripRelease
{
    protected $trip_IDs;
    protected $uid;

    //失败重试次数
    public $tries = 1;

    public function __construct($trip_IDs, $uid)
    {
        $this->trip_IDs = $trip_IDs;
        $this->uid = $uid;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //3. 计算出路线Key
        //List => array(Redis_key => trip_ID)

        $List = array();
        foreach ($this->trip_IDs as $key => $trip_ID) {
            $commuteInfo = PublishTrip::select('trip_release.id as trip_id', 'trip_release.status', 'trip_release.publish_time', 'passenger_commute_route.*')
                ->leftJoin('passenger_commute_route',function($join){
                    $join->on('trip_release.commute_id', '=', 'passenger_commute_route.id');
                })
                ->where('trip_release.id', $trip_ID)
                ->get()
                ->toArray();

            $origin_code = $this->getAddress($commuteInfo[0]['origin_longitude'], $commuteInfo[0]['origin_latitude']);
            $destination_code = $this->getAddress($commuteInfo[0]['destination_longitude'], $commuteInfo[0]['destination_latitude']);

            //计算得到Redis-Key
            $base_key = "driver:origin:$origin_code:destination:$destination_code";
            $start_time = $this->getHour($commuteInfo[0]['depart_time']);
            $end_time = $this->getHour($commuteInfo[0]['arrive_time']);
            $redis_key = $base_key . ':str:' . $start_time . ':end:' . $end_time;

            // 4. 根据Key获取到司机路线
            $driverRouteList = DriverRoute::where('redis_key',$redis_key)->get()->toArray();
            // 5. 计算获取司机路线
            foreach($driverRouteList as $driverRoute){

                $driver_origin_long = $driverRoute['origin_longitude'];
                $driver_origin_lat = $driverRoute['origin_latitude'];

                $driver_destination_long = $driverRoute['destination_longitude'];
                $driver_destination_lat = $driverRoute['destination_latitude'];

                // 计算出行路线
                $data[$driverRoute['id']] = $this->computedRoute($driver_origin_long, $driver_origin_lat, $driver_destination_long, $driver_destination_lat);
            }
            // 计算自己路线
            $passengerRoute = $this->computedRoute($commuteInfo[0]['origin_longitude'],$commuteInfo[0]['origin_latitude'],$commuteInfo[0]['destination_longitude'],$commuteInfo[0]['destination_latitude']);

            // 6. 计算匹配度
            $rates = $this->computedMatchRate($data, $passengerRoute);

            foreach ($rates as $commute_id => $rate){
                if($rate > 70){
                    $List[$redis_key] = ['trip_id' => $trip_ID, 'commute_id' => $commute_id];
                }
            }

            //6. 行程ID放入Redis集合中
            //Redis集合Key为 driver:origin:6位:destination:6位:str:08:end:09:trip
            app('redis')->sadd($redis_key . ":trip",$trip_ID);

            //7. 添加redis-key冗余
            app('redis')->set('passenger:' . $this->uid . ':trip:' . $trip_ID, $redis_key . ":trip");
        }


        //4. 根据Key推送信息
        foreach ($List as $item) {
            //推送信息
            $this->publishTrip($key, $item);
        }

    }

    //5. 向指定的频道推送信息
    private function publishTrip($key, $content)
    {
        //循环获取Redis队列的中司机信息,并推送信息
        $url = 'http://' . env('GO_REST_HOST') . '/publish';

        $data = array(
            'appkey'    => env('GO_COMMON_KEY'),
            'channel'   => $key,
            'content'   => $content['trip_id'] . ',' . $content['commute_id']
        );
        //发送推送请求
        $res = $this->posturl($url, $data);
    }

    // 计算乘客对应Redis-Key值
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

    // 计算司机出行路线
    private function computedRoute($origin_long, $origin_lat, $destination_long, $destination_lat)
    {
        // webapi轻量级算路
        $ak = env('BAIDU_MAP_AK');
        $url = "http://api.map.baidu.com/directionlite/v1/driving?origin=$origin_lat,$origin_long&destination=$destination_lat,$destination_long&ak=$ak";
        $data = $this->geturl($url);
        return $data['result']['routes'][0]['steps'];
    }


    // 计算匹配度
    private function computedMatchRate($driverRoutes, $passengerRoute)
    {
        $rate = [];
        $passengerTempStr = '';
        foreach ($passengerRoute as $step => $detail){
            $passengerTempStr .= implode(',' ,$detail['end_location']) . ',';
        }

        foreach($driverRoutes as $commute_id => $driverRoute){
            $driverTempStr = '';
            foreach ($driverRoute as $step => $detail){
                $driverTempStr .= implode(',', $detail['end_location']) . ',';
            }
            $rate[$commute_id] = similar_text($passengerTempStr, $driverTempStr);
        }
        return $rate;
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

    private function posturl($url,$data){

        $ch = curl_init();
        curl_setopt ( $ch, CURLOPT_URL, $url );//地址
        curl_setopt ( $ch, CURLOPT_POST, 1 );//请求方式为post
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );//不打印header信息
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );//返回结果转成字符串
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );//post传输的数据。
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output,true);
    }

    private function getHour($time){
        return substr($time, 0, 2);
    }
}
