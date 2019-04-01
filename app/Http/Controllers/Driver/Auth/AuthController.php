<?php

namespace App\Http\Controllers\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Service\AuthService;
use Illuminate\Http\Request;


class AuthController extends Controller
{
	protected $authService;

	public function __construct(AuthService $authService){
		$this->authService = $authService;
	}

	/**
	 * 司机真实身份验证
	 */
	public function identifyAuth(Request $request)
	{	
		$res = $this->authService->identifyAuth($request);
		return response()->json($res);
	}

	/**
	 * 司机驾驶证验证
	 */
	public function licenceAuth(Request $request)
	{
		$res = $this->authService->licenceAuth($request);
		return response()->json($res);
	}

	/**
	 * 车辆行驶证验证
	 */
	public function vehicleAuth(Request $request)
	{
		$res = $this->authService->vehicleAuth($request);
		return response()->json($res);
	}

	/**
	 * 司机登录
	 */
	public function driverLogin(Request $request){

		$res = $this->loginService->driverLogin($request);
		return response()->json($res);
	}

}
