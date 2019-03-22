<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Service\LoginService;

class LoginController extends Controller
{
    
	protected $loginService;
	public function __construct(LoginService $loginService){
		$this->loginService = $loginService;
	}

	/**
	 * 乘客登录
	 */
	public function passengerLogin(Request $request){

		$res = $this->loginService->passengerLogin($request);

		return response()->json($res);
	}

}
