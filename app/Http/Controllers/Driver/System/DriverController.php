<?php

namespace App\Http\Controllers\Driver\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Service\System\DriverService;
use Illuminate\Support\Facades\Auth;
use App\Model\Travel\DriverRoute;
use App\Model\Travel\TripRecord;
use App\Model\Travel\PublishTrip;

class DriverController extends Controller
{

	protected $driverService;

	public function __construct(DriverService $driverService)
	{
		$this->driverService = $driverService;
	}

	/**
	 * 用户注册
	 * @param  Request $request [表单参数]
	 * @return [json]           [数据库插入结果]
	 */
	public function register(Request $request)
	{	
		$res = $this->driverService->addDriver($request);
		return response()->json($res);
	}


	/**
	 * 发送验证短信
	 */
	public function sendMessage(Request $request)
	{
		$res = $this->driverService->sendCode($request);
		return response()->json($res);
	}


	/**
	 * 获取司机信息
	 */
	public function getDriverInfo(){
		$res = Auth::guard('motorman')->user();
		return response()->json($res);
	}


	/**
	 * 获取车辆信息
	 */
	public function getVehicleInfo(){
		$res = $this->driverService->getVehicleInfo();
		return response()->json($res);
	}

	//改变当前车辆
	public function changeVehicle(Request $request){
		$res = $this->driverService->changeVehicle($request);
		return $res;
	}


	/**
	 * 设置上下班路线
	 * @param $localeType 
	 */
	public function setRoute(Request $request)
	{
		$res = $this->driverService->setDriverRoute($request);
		if($res){
			return array('status' => '10000', 'message' => '设置成功', 'data' => $res);
		}else{
			return array('status' => '10001', 'message' => '设置失败');
		}
	}

	//获取上下班路线信息
	public function getRoute(Request $request)
	{
		$route_type = $request->input('route_type');
		$driver = Auth::guard('motorman')->user();
		if($route_type == 'work'){
			$res = DriverRoute::where(['did' => $driver->did, 'locale_type' => 1])->get()->toArray();
		}else if($route_type == 'offwork'){
			$res = DriverRoute::where(['did' => $driver->did, 'locale_type' => 2])->get()->toArray();
		}else{
			$res = DriverRoute::where(['did' => $driver->did])->get()->toArray();
		}

		return $res;
	}

	//开启订单监听
	public function openListen(Request $request)
	{	
		//接收Redis_key数组
		$redis_key = $request->input('redis_key');
		$res = $this->driverService->openListen($redis_key);
		return $res;
	}

	//获取订单信息
	public function getOrder()
	{	
		
		$driver = Auth::guard('motorman')->user();
		$res = TripRecord::where(['did' => $driver->did, 'status' => 0])->get()->toArray();

		return $res;
	}

	//根据release_id获取用户路线
	public function getPassengerRoute(Request $request)
	{
		$driver = Auth::guard('motorman')->user();
		//接收trip_id
		$trip_id = $request->input('trip_id');
		//接收司机commute_id
		$commute_id = $request->input('commute_id');

		//连表查询,最终获取用户的commute_route信息,连表获取获得用户信息
		$currentInfo = PublishTrip::select('passenger_commute_route.*','user.mobile','user.sex','user.realname')
		->leftJoin('passenger_commute_route',function($join){
			$join->on('passenger_commute_route.id', '=', 'trip_release.commute_id');
		})->leftJoin('user',function($join){
			$join->on('trip_release.uid', '=', 'user.uid');
		})
		->where('trip_release.id',$trip_id)
		->first();
 
 		//其他顺路的路线及用户信息
 		//即commute_trip_record表中相同司机,相同commute_id的路线
		$hitchInfo = TripRecord::select('passenger_commute_route.*','user.mobile','user.sex','user.realname')
		->leftJoin('trip_release', function($join){
			$join->on('trip_release.id', '=', 'commute_trip_record.release_id');
		})->leftJoin('passenger_commute_route',function($join){
			$join->on('passenger_commute_route.id', '=', 'trip_release.commute_id');
		})->leftJoin('user',function($join){
			$join->on('trip_release.uid', '=', 'user.uid');
		})->where(['commute_trip_record.did' => $driver->did, 'commute_trip_record.status' => 0, 'commute_trip_record.driver_commute_id' => $commute_id])
		->get()
		->toArray();

		//获取司机路线信息
		$driverRoute = DriverRoute::find($commute_id);

		$res = array(
			'currentInfo' => $currentInfo,
			'hitchInfo'   => $hitchInfo,
			'driverRoute' => $driverRoute
		);

		return $res;
	}


	//司机下单
	public function bookOrder(Request $request)
	{
		$trip_id = $request->input('trip_id');
		$distance = $request->input('distance');
		$commute_id = $request->input('driver_commute_id');
		$person = $request->input('person');
		$res = $this->driverService->createOrder($trip_id, $distance, $commute_id, $person);

		return $res;
	}


	//获取订单详细信息
	public function getOrderDetail(Request $request)
	{
		//获取record_id
		$record_ids = $request->input('record_ids');
		//$record_ids = @json_decode($record_ids,true);

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
		//获取顺路路线信息
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
			
			$driverRoute = DriverRoute::find($value['driver_commute_id']);

			$tmp = array(
				'currentInfo' => $value,
				'hitchInfo'   => $hitchInfo,
				'driverRoute' => $driverRoute
			);
			array_push($res, $tmp);

		}


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