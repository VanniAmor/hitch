<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Service\LoginService;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    
	protected $loginService;
	public function __construct(LoginService $loginService){
		$this->loginService = $loginService;
	}

	/**
	 * 乘客登录
	 */
	public function passengerLogin(Request $request)
	{
		$res = $this->loginService->passengerLogin($request);
		return response()->json($res);
	}


	/**
	 * 司机登录
	 */
	public function driverLogin(Request $request)
	{
		$res = $this->loginService->driverLogin($request);
		return response()->json($res);
	}

	/**
	 * 司机Token刷新
	 */
	public function driverRefresh(Request $request)
	{
		//Token无效
		if($token = Auth::guard('motorman')->parseToken()->refresh()){
			return array('status' => 60000,'message'=> 'Token刷新','data' => array('bearer' . $token));
		}else{
			//刷新无效,重新登录
			return array('status' => 60001,'message' => '登录信息失效,请重新登录');
		}
		
	}
}
