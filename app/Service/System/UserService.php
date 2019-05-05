<?php 	

namespace App\Service\System;

use Illuminate\Http\Request;
use App\Utils\SMS\SendTemplateSMS;
use Illuminate\Support\Facades\Auth;
use App\Model\System\User;
use App\Model\Travel\PassengerRoute;
use App\Model\Travel\PublishTrip;
use App\Model\System\TempPhone;
use Illuminate\Support\Facades\DB;
use App\Jobs\TripRelease;
use App\Model\Travel\TripRecord;
use App\Model\Order\DriverServiceInfo;
/*use App\Service\System\TripRelease;*/
/*driver:origin:440106:destination:440106:str:08:end:09:trip*/

class UserService{
	/**
	 * 添加用户
	 * @param Request $request [表单数据]
	 */
	public function addUser(Request $request)
	{
		//验证短信
		$res = $this->verifyCode($request);
		if($res !== true){
			return $res;
		}
		$data = array(
			'mobile' 		=> $request->input('mobile'),
			'nickname'		=> '用户' . $request->input('mobile'),
			'password'		=> password_hash($request->input('password'),PASSWORD_DEFAULT),
			'sex'			=> $request->input('sex')
		);

		if(User::where('mobile',$data['mobile'])->first())
			return array('status' => 90001, 'message' => '用户已经存在');

		//获取IP，判断所在区域
		$ip = $request->getClientIp();
		$region_num = '110000';
		$data['region_num'] = $region_num;
		return User::create($data);
	}

	/**
	 * 发送短信验证码
	 */
	public function sendCode(Request $request)
	{
		//检测用户是否存在
		$mobile = $request->input('mobile');
		$user = User::where('mobile',$mobile)->first();
		if($user){
			//用户已经存在
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


	//设置用户上下班路线
	public function setPassengerRoute(Request $request){
		$user = Auth::guard('passenger')->user();
		$route_type = $request->input('routeType') == 'work' ? 1 : 2;

		$condition = array(
			'uid' => $user->uid,
			'locale_type' => $route_type
		);

		$data = array(
			'origin' => $request->input('origin'),
			'origin_longitude' => $request->input('origin_longitude'),
			'origin_latitude'  => $request->input('origin_latitude'),
			'destination' => $request->input('destination'),
			'destination_longitude'  => $request->input('destination_longitude'),
			'destination_latitude'   => $request->input('destination_latitude'),
			'depart_time' => $request->input('depart_time'),
			'arrive_time' => $request->input('arrive_time') ?? '',
			'person' => $request->input('person')
		);

		//信息入库
		$passengerRoute = PassengerRoute::where($condition)->first();

		if($passengerRoute){
			//更新
			return $passengerRoute->update($data);
		}else{
			//插入数据
			return  PassengerRoute::create(array_merge($condition,$data));
		}

	}


	/**
	 * 乘客发布行程
	 * 1. 获取乘客路线信息
	 * 2. 行程信息入库
	 * 
	 * 3. 计算出路线Key
	 * 4. 根据Key获取到司机列表
	 * 5. 把行程ID trip_id推送到司机队列中,推送频道名称为计算出的Key
	 * 6. 行程ID放入Redis集合中
	 *
	 * 步骤3-6 是可以异步执行的,放置到消息队列中执行
	 *
	 * @param $type 3-发布上下班路线, 2-发布下班路线, 1-发布上班路线
	 * 
	 */
	public function publishTrip($type)
	{
		//1. 获取到乘客路线信息
		$passenger = Auth::guard('passenger')->user();
		$commute_list = PassengerRoute::where(['uid' => $passenger->uid])->get()->toArray();
		$commuteInfo = null;
		if(!$commute_list) return false;

		$data = array();

		// 根据用户设置,获取路线信息
		if($type == 3){
			//插入所有路线信息
			foreach ($commute_list as $key => $value) {
				$data[] = array('uid' => $passenger->uid, 'commute_id' => $value['id'], 'publish_time' => date('Y-m-d H:i:s',time()));
			}
			$commuteInfo = $commute_list;
		}else{
			//根据type插入单条数据
			foreach ($commute_list as $key => $value) {
				if($value['locale_type'] == $type){
					$data[] = array('uid' => $passenger->uid, 'commute_id' => $value['id'], 'publish_time' => date('Y-m-d H:i:s',time()));
					$commuteInfo = $value;
				}
			}
			if(!$data){
				return array('status' => 10001, 'message' => '发布失败');
			}
		}

		//2. 行程信息入库(没有提供批量插入的同时获取插入的ID的方法,手动处理)
		//$IDs = Db::table('trip_release')->insert($data);
		$IDs = array();
		foreach ($data as $key => $value) {
			$ID = PublishTrip::insertGetId($value);
			$IDs[] = $ID;
		}

		//3. 行程ID放到Redis集合中(测试使用,到时直接使用消息队列)
		dispatch(new TripRelease($IDs, $passenger->uid));
		/*$test = new TripRelease($IDs);
		$test->handle();*/

		return array('status' => 10000, 'message' => '发布成功', 'trip_ids' => $IDs);
	}

	//乘客确定上车
	public function confirmBoarding(Request $request)
	{	
		$user = Auth::guard('passenger')->user();
		$record_id = $request->input('record_id');
		$rate = $request->input('rate', 3.8);
		//开启数据库事务
		DB::beginTransaction();
		try {
			//记录表状态更改
			$record = TripRecord::find($record_id);
			$record->depart_time = date('Y-m-d H:i:s',time());
			$record->status = 1;
			$record->save();

			//路线状态更改
			$trip_release = PublishTrip::find($record->release_id);
			$trip_release->status = 2;
			$trip_release->save();

			//计算司机服务信息
			$driverServiceInfo = DriverServiceInfo::firstOrNew(['did' => $record->did]);
			//总服务人数
			$driverServiceInfo->total_service += 1;
			//司机余额计算
			$driverServiceInfo->remaining_sum += $record->count * 0.95;
			$driverServiceInfo->save();

			//操作成功
			Db::commit();
			return array('status' => 10000, 'message' => '操作成功');
		} catch (Exception $e) {
			Db::rollBack();
			return array('status' => 10001, 'message' => '失败');
		}
	}

	//评价司机
	public function evaluateDriver(Request $request)
	{	
		/*$record_id = $request->input('record_id');
		$rate = $request->input('rate', 3.8);

		DB::beginTransaction();
		try {
			//修改记录评价
			$record = TripRecord::find($record_id);
			$record->grade = $rate;
			$record->save();
	
			//计算司机服务信息
			$driverServiceInfo = DriverServiceInfo::find(['did' => $record->did]);
			//总评分
			$totalRate = ($driverServiceInfo->total_service) * $driverServiceInfo->service_grade + $rate;
			//平均服务评分
			$driverServiceInfo->service_grade = round($totalRate / $driverServiceInfo->total_service, 2);
			$driverServiceInfo->save();
			
			Db::commit();
			return array('status' => 10000, 'message' => '操作成功');
		}catch{
			Db::rollBack();
			return array('status' => 10001, 'message' => '失败');
		}*/

	}

	public function test($data){
		return $data;
	}
}