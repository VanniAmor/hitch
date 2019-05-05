<?php 	

namespace App\Service\System;

use Illuminate\Http\Request;
use App\Model\System\Driver;
use App\Utils\SMS\SendTemplateSMS;
use App\Model\System\TempPhone;
use Illuminate\Support\Facades\Auth;
use App\Model\Licence\VehicleLicence;
use App\Model\Licence\CertificateComparison;
use App\Model\Travel\DriverRoute;
use App\Service\DriverChannel;
use App\Model\Travel\PublishTrip;
use Illuminate\Support\Facades\DB;

class DriverService
{

	/**
	 * 添加用户
	 * @param Request $request [表单数据]
	 */
	public function addDriver(Request $request)
	{	
		//验证短信
		$res = $this->verifyCode($request);
		if($res !== true){
			return $res;
		}
		$data = array(
			'mobile' 		=> $request->input('mobile'),
			'password'		=> password_hash($request->input('password'),PASSWORD_DEFAULT),
			'sex'			=> $request->input('sex')
		);

		if(Driver::where('mobile',$data['mobile'])->first())
			return array('status' => 90001, 'message' => '用户已经存在');

		return Driver::create($data);
	}

	/**
	 * 发送短信验证码
	 */
	public function sendCode(Request $request)
	{
		//检测司机是否存在
		$mobile = $request->input('mobile');
		$user = Driver::where('mobile',$mobile)->first();
		if($user){
			//司机已经存在
			return array('status' => 90001,'message' => '用户已经存在');
		}
 		//发送短信
	    $sendTemplateSMS = new SendTemplateSMS;
	    $code = mt_rand(1000,9999);
	    //短信服务已过期！！
	    //$res = $sendTemplateSMS->sendTemplateSMS($mobile, array($code, 60), 1);
	    $res = array('status' => 0, 'message' => '发送成功');

	    if($res['status'] == 0){
	    	//数据入库
	    	TempPhone::updateOrCreate(
	    		['mobile' => $mobile,],
	    		['status' => 0, 'send_time' => date('Y-m-d H-i-s'), 'code' => $code]
	    	);
	    	return array('status' => 200, 'message' => '发送成功!');
	    }
	    return array('status' => 90006, 'message' => '发送失败!');
	}


	/**
	 * 验证短信验证码
	 */
	private function verifyCode(Request $request)
	{
		$mobile = $request->input('mobile');
		$message_code = $request->input('message_code');

		if(!$message_code)
			return array('status' => 90007, 'message' => '请输入短信验证码');

		$TempPhone = TempPhone::where('mobile', $mobile)->first();
		if(!$TempPhone) 
			return array('status' => 90002, 'message' => '请输入正确的手机');
	
		if($TempPhone->status == 1)
			return array('status' => 90003, 'message' => '验证码已失效');

		//15分钟内有效
		$time = strtotime('+15minutes',strtotime($TempPhone->send_time));
		if(time() >= $time )
			return array('status' => 90004, 'message' => '验证码超时');
		
		if($TempPhone->code != $message_code)
			return array('status' => 90005, 'message' => '验证码不正确');

		//更新数据库
		$TempPhone->status = 1;
		$TempPhone->save();
		return true;
	}
	

	/**
	 * 获取车辆信息
	 */
	public function getVehicleInfo()
	{
		$driver = Auth::guard('motorman')->user();
		$vehicleInfo = VehicleLicence::select('vehicle_licence_info.*','vehicle_licence_img.licence_img_url','vehicle_licence_img.vehicle_img_url', 'certificate_comparison.using', 'certificate_comparison.checked')
		->leftJoin('certificate_comparison',function($join){
			$join->on('vehicle_licence_info.id', '=', 'certificate_comparison.licence_id');
		})->leftJoin('vehicle_licence_img',function($join){
			$join->on('vehicle_licence_img.vid', '=', 'vehicle_licence_info.id');
		})
		->where('certificate_comparison.did',$driver->did)
		->get();
		return $vehicleInfo;
	}

	
	/**
	 * 更改行驶车辆
	 */
	public function changeVehicle(Request $request)
	{	
		$licence_id = $request->input('licence_id');
		$driver = Auth::guard('motorman')->user();
		$vehicle = CertificateComparison::leftJoin('vehicle_licence_info',function($join){
			$join->on('vehicle_licence_info.id', '=', 'certificate_comparison.licence_id');
		})->where('certificate_comparison.did',$driver->did)->get();


		foreach ($vehicle as $key => $value) {
			if($value->licence_id == $licence_id){
				$value->using = 1;
				$value->save();
				continue;
			}
			$value->using = 0;
			$value->save();
		}
		return [];
	}



	/**
	 * 司机上下班路线设置
	 * @param $ip         [用户IP]
	 */
	public function setDriverRoute($request)
	{	
		$driver = Auth::guard('motorman')->user();
		$route_type = $request->input('routeType') == 'work' ? 1 : 2;

		$condition = array(
			'did' => $driver->did,
			'locale_type' => $route_type
		);

		$data = array(
			'origin' => $request->input('origin'),
			'origin_longitude' => $request->input('origin_longitude'),
			'origin_latitude'  => $request->input('origin_latitude'),
			'destination' => $request->input('destination'),
			'destination_longitude'  => $request->input('destination_longitude'),
			'destination_latitude'   => $request->input('destination_latitude'),
			'depart_time' =>  $route_type == 1 ? $request->input('depart_time') : $request->input('off_time'),
			'arrive_time' => $request->input('arrive_time'),
			'person' => $request->input('person')
		);


		//信息入库
		$driverRoute = DriverRoute::where($condition)->first();
		if($driverRoute){
			//更新信息
			$driverRoute->update($data);
			//需要实时计算,不能使用消息队列
			$channel = new DriverChannel($driverRoute,true);
			$redis_key = $channel->handle();
		}else{
			//新建信息
			//需要实时计算,不能使用消息队列
			$driverRoute = DriverRoute::create(array_merge($condition,$data));
			$channel = new DriverChannel($driverRoute,true);
			$redis_key = $channel->handle();
		}

		//返回Redis—key作为推送频道名称
		return array('redis_key' => $redis_key, 'commute_id' => $driverRoute->id);
	}


	/**
	 * 接收订单,开启接单模式
	 * 1. 检测司机审核状态
	 * 2. 到Redis集合中查找是否有订单
	 * 3. 若集合中找到,返回
	 * 4. 无找到订单,把司机ID加入到Redis监听队列中
	 */
	public function openListen($redis_key)
	{
		$driver = Auth::guard('motorman')->user();
		//1. 判断是否通过审核
		if($driver->usable == 0 || $driver->checked == 0){
			return array('status' => 10000, 'message' => '您还未通过系统审核');
		}
		
		//2. 从Redis集合中查找是否有发布的路线
		
		$set_key = $redis_key . ':trip';
		$trip_id = app('redis')->srandmember($set_key,1);
		if($trip_id){
			//3. 若集合中找到, 返回
			return array('status' => 10001, 'data' => $trip_id, 'message' => 'set中查找到订单');
		}

		//4. 把司机ID添加到Redis监听队列中
		//app('redis')->lpush($driver->redis_key, $driver->did);
		return array('status' => 10002, 'data' => null, 'message' => '添加到监听队列');
	}



	/**
	 * 确认订单
	 * 这里涉及高并发,只能使用队列处理【高并发程度不高，使用文件锁解决并发问题】
	 * 订单信息(金额,行驶距离等)由前端计算,并上传到该接口
	 * 
	 * 1. 删除Redis集合中对应元素
	 * 2. 修改trip_release中对应数据的状态
	 * 3. 创建订单信息,commute_trip_record
	 * 4. 前端调用 关闭接单接口
	 * 
	 * @param $trip_id 用户行程ID
	 * @param $distance 距离
	 * @param $commute_id 司机路线ID
	 * @param $person 人数
	 */
	public function createOrder($trip_id, $distance, $commute_id, $person)
	{	
		$driver = Auth::guard('motorman')->user();
		//开启文件锁
		$fp = fopen('../order.lock','r');
		flock($fp, LOCK_EX);
		
		//查询订单状态
		$passenger_trip = PublishTrip::find($trip_id);
		if($passenger_trip->status == 1) {
			return array('status' => 10001, 'message' => '该行程已被其他司机接单');
		}

		//计算金额,5公里3元,超过5公里的每公里收费1.4元,多一个人多收50%
		if($distance >= 5 ){
			$count = ($distance - 5) * 1.4 + 3;
			$count = ($person + 1) * 0.5 * $count;
		}else{
			$count = 3;
		}

		//开启事务,自动事务
		DB::beginTransaction();
		
		//1. 生成订单
		
		$insert_res = DB::table('commute_trip_record')->insertGetId(
			['release_id' => $trip_id, 'driver_commute_id' => $commute_id, 'did' => $driver->did, 'count' => $count, 'distance' => $distance]);
		//2. 修改用户行程状态
		$update_res = DB::table('trip_release')->where('id', $trip_id)->update(['status' => 1]);
		if($update_res && $insert_res){
			//提交数据库事务
			DB::commit();
		}else{
			//回滚
			DB::rollBack();
			return array('status' => 10002, 'message' => '抢单失败');
		}

		//删除redis集合中对应的元素
		$driver_commute_route = DB::table('driver_commute_route')->where('id',$commute_id)->get();
		$redis_key = $driver_commute_route[0]->redis_key;
		app('redis')->srem($redis_key, $trip_id);
		//删除redis的冗余key
		app('redis')->del('passenger:' . $passenger_trip->uid . ':trip:' . $trip_id);

		//关闭文件锁
		flock($fp, LOCK_UN);
		fclose($fp);
		

		//GoEasy发送给客户, $insert_res => record_id
		$this->publishTrip($trip_id, $insert_res);
		return array('status' => 10000, 'message' => '抢单成功');
	}

	private function publishTrip($key, $record_id)
    {
        //循环获取Redis队列的中司机信息,并推送信息
        $url = 'http://' . env('GO_REST_HOST') . '/publish';

        $data = array(
            'appkey'    => env('GO_COMMON_KEY'),
            'channel'   => $key,
            'content'   => $record_id
        );
        //发送推送请求
        $res = $this->posturl($url, $data);
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

}