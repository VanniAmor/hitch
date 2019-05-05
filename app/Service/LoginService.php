<?php

namespace App\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWTAuth;

class LoginService
{
	protected $jwt;
	public function __construct(JWTAuth $jwt){
		$this->jwt = $jwt;
	} 

	/**
	 * 乘客登录
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function passengerLogin(Request $request)
	{
		$data = $request->only('mobile','password');

		//$token = $this->jwt->auth('passenger')->attempt($data);
		$token = Auth::guard('passenger')->attempt($data);
		$user = Auth::guard('passenger')->user();

		return [
			'token' => 'bearer' . $token,
			'uid'	=> $user->uid
		];
	}

	/**
	 * 司机登录
	 */
	public function driverLogin(Request $request)
	{
		$data = $request->only('mobile','password');
		//$token = $this->jwt->guard('driver')->attempt($data);
		$token = Auth::guard('motorman')->attempt($data);
		$driver = Auth::guard('motorman')->user();
		return [
			'token' => 'bearer' . $token,
			'did'	=> $driver->did
		];
	}

}