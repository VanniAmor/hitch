<?php

namespace App\Http\Controllers\Passenger\Auth;

use App\Http\Controllers\Controller;
use App\Service\AuthService;
use Illuminate\Http\Request;


class AuthController extends Controller
{
	protected $authService;

	public function __construct(){
		//userType = 2, 用户类型为乘客
		$this->authService = new AuthService(2);
	}

	/**
	 * 用户真实身份验证
	 */
	public function identifyAuth(Request $request)
	{	
		$res = $this->authService->identifyAuth($request);
		return response()->json($res);
	}


}
