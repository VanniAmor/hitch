<?php

namespace App\Http\Controllers\Passenger\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Service\System\UserService;
use Illuminate\Support\Facades\Auth;
use App\Model\System\User;
use App\Model\Travel\PassengerRoute;
use App\Model\Travel\PublishTrip;
use App\Model\Travel\TripRecord;
use Illuminate\Support\Facades\DB;
use App\Model\Travel\DriverRoute;
use App\Model\Order\DriverServiceInfo;

class UserController extends Controller
{

	protected $userService;

	public function __construct(UserService $userService)
	{
		$this->userService = $userService;
	}

	/**
	 * 用户注册
	 * @param  Request $request [表单参数]
	 * @return [json]           [数据库插入结果]
	 */
	public function register(Request $request)
	{
		$res = $this->userService->addUser($request);
		return response()->json($res);
	}

	/**
	 * 发送验证短信
	 */
	public function sendMessage(Request $request)
	{
		$res = $this->userService->sendCode($request);
		return response()->json($res);
	}

	/**
	 * 获取用户信息
	 */
	public function getUserInfo(){
		$user = Auth::guard('passenger')->user();
		return $user;
	}


	/**
	 * 设置上下班路线
	 * @param $localeType
	 * 方案一：根据用户起点终点判断所在区域
	 * 方案二：使用ip获取用户所在定位
	 */
	public function setRoute(Request $request)
	{	
		$res = $this->userService->setPassengerRoute($request);
		if($res){
			return array('status' => '10000', 'message' => '设置成功');
		}else{
			return array('status' => '10001', 'message' => '设置失败');
		}
		
	}

    // 获取附近上车点
    public function getNearBy(Request $request)
    {
        $res = $this->userService->getNearBy($request);
        return $res;
    }

	/**
	 * 获取上下班路线
	 * @return [type] [description]
	 */
	public function getRoute(Request $request)
	{	
		$route_type = $request->input('route_type');
		$user = Auth::guard('passenger')->user();
		if($route_type == 'work'){
			$res = PassengerRoute::where(['uid' => $user->uid, 'locale_type' => 1])->get()->toArray();
		}else if($route_type == 'offwork'){
			$res = PassengerRoute::where(['uid' => $user->uid, 'locale_type' => 2])->get()->toArray();
		}else{
			$res = PassengerRoute::where(['uid' => $user->uid])->orderBy('locale_type')->get()->toArray();
		}

		return $res;
	}


	/**
	 * 用户发布行程
	 * @param $type 行程类型 1-上班 2-下班 3-上下班
	 */
	public function publishTrip(Request $request)
	{
		$type = $request->input('type');
		$res = $this->userService->publishTrip($type);

		return $res;
	}

	/**
	 * 用户取消行程
	 * @param $trip_id 行程ID
	 */
	public function cancelTrip(Request $request)
	{
		$trip_ids = $request->input('trip_ids');
		//$trip_ids = json_decode($trip_ids,true);
		$user = Auth::guard('passenger')->user();
		try {
			foreach ($trip_ids as $key => $trip_id) {
				//删除redis集合中的记录
				$redis_key = app('redis')->get('passenger:' . $user->uid . ':trip:' . $trip_id);
				app('redis')->srem($redis_key,$trip_id);
				app('redis')->del('passenger:' . $user->uid . ':trip:' . $trip_id);
				//修改数据状态
				$trip = PublishTrip::find($trip_id);
				$trip->status = -1;
				$trip->save();
			}
			return array('status' => 10000, 'message' => '成功取消订单');
		} catch (Exception $e) {
			return array('status' => 10001, 'message' => '取消订单失败');
		}
	}


	//获取订单信息
	public function getOrder(Request $request)
	{
		$user = Auth::guard('passenger')->user();
		//连表查询出发布且被接单的路线
		$res = PublishTrip::select('commute_trip_record.id as record_id')
		->leftJoin('commute_trip_record',function($join){
			$join->on('commute_trip_record.release_id', '=', 'trip_release.id');
		})->where(['trip_release.status' => 1, 'uid' => $user->uid])->get();

		return $res;
	}

	//获取订单详情
	public function getOrderDetail(Request $request)
	{
		//获取record_id
	    $record_ids = $request->input('record_ids');
		//$record_ids = json_decode($record_ids,true);
		
		//获取当前订单的路线信息
		$currentInfo = TripRecord::select('commute_trip_record.id as record_id', 'commute_trip_record.driver_commute_id', 'passenger_commute_route.*','user.mobile','user.sex','user.realname')->leftJoin('trip_release', function($join){
			$join->on('trip_release.id', '=', 'commute_trip_record.release_id');
		})->leftJoin('passenger_commute_route',function($join){
			$join->on('passenger_commute_route.id', '=', 'trip_release.commute_id');
		})->leftJoin('user',function($join){
			$join->on('trip_release.uid', '=', 'user.uid');
		})->whereIn('commute_trip_record.id', $record_ids)
		->get()
		->toArray();

		$res = array();
		$hitchID_list = array();

		
		foreach ($currentInfo as $key => $value) {
			if(in_array($value['record_id'], $hitchID_list)){
				continue;
			}
			//获取顺路路线
			$hitchInfo = TripRecord::select('commute_trip_record.id as record_id','passenger_commute_route.*','user.mobile','user.sex','user.realname')
				->leftJoin('trip_release', function($join){
					$join->on('trip_release.id', '=', 'commute_trip_record.release_id');
				})->leftJoin('passenger_commute_route',function($join){
					$join->on('passenger_commute_route.id', '=', 'trip_release.commute_id');
				})->leftJoin('user',function($join){
					$join->on('trip_release.uid', '=', 'user.uid');
				})->where(['commute_trip_record.status' => 0, 'commute_trip_record.driver_commute_id' => $value['driver_commute_id']])
				->WhereNotIn('commute_trip_record.id' , [$value['record_id']])
				->get()
				->toArray();
			foreach ($hitchInfo as $k => $v) {
				array_push($hitchID_list, $v['record_id']);
			}
			
			$driverRoute = DriverRoute::select('driver_commute_route.*','driver.realname','driver.sex','driver.mobile', 'vehicle_licence_info.plate_num', 'vehicle_licence_info.color', 'vehicle_licence_info.car_brand','driver_service_info.total_service', 'driver_service_info.service_grade')
			->leftJoin('driver',function($join){
				$join->on('driver.did', '=' , 'driver_commute_route.did');
			})->leftJoin('certificate_comparison',function($join){
				$join->on('certificate_comparison.did', '=' , 'driver_commute_route.did');
			})->leftJoin('vehicle_licence_info',function($join){
				$join->on('certificate_comparison.licence_id', '=' , 'vehicle_licence_info.id');
			})->leftJoin('driver_service_info',function($join){
				$join->on('driver_service_info.did', '=' , 'driver_commute_route.did');
			})->where(['driver_commute_route.id' => $value['driver_commute_id'], 'certificate_comparison.using' => 1])
			->get();

			$tmp = array(
				'currentInfo' => $value,
				'hitchInfo'   => $hitchInfo,
				'driverRoute' => $driverRoute
			);
			array_push($res, $tmp);
		}

		return $res;

	}

	//获取订单列表
	public function getRouteList(Request $request)
	{	
		//起点、终点、发布时间
		$status = $request->input('status');
		$user = Auth::guard('passenger')->user();

		$records = TripRecord::select('commute_trip_record.id as record_id','trip_release.publish_time','passenger_commute_route.origin','passenger_commute_route.destination')
			->leftJoin('trip_release',function($join){
				$join->on('trip_release.id', '=', 'commute_trip_record.release_id');
			})->leftJoin('passenger_commute_route', function($join){
				$join->on('passenger_commute_route.id', '=', 'trip_release.commute_id');
			})
			->where(['commute_trip_record.status' => $status, 'trip_release.uid' => $user->uid])
			->paginate(5);

		return $records;
	}

	//用户确认上车
	public function confirmOrder(Request $request){
		$res = $this->userService->confirmBoarding($request);
		return $res;
	}

	//用户评价司机
	public function evaluateDriver(Request $request){
		$res = $this->evaluateDriver($request);
		return $res;
	}

	//取消订单
	public function delOrder(Request $request)
    {
        $res = $this->userService->delOrder($request);
        return $res;
    }

	//获取用户IP地址
	private function getClientIP() {
    	//strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
	    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	        $ip = getenv('HTTP_CLIENT_IP');
	    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	        $ip = getenv('HTTP_X_FORWARDED_FOR');
	    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	        $ip = getenv('REMOTE_ADDR');
	    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	        $ip = $_SERVER['REMOTE_ADDR'];
	    }
	    $res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
	    return $res;
	}


}