<?php

namespace App\Http\Controllers\Passenger\Auth;

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
	 * 用户真实身份验证
	 */
	public function identifyAuth(Request $request)
	{	
		$res = $this->authService->identifyAuth($request);
		return response()->json($res);
	}


}
