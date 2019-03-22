<?php

namespace App\Http\Controllers\Passenger\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Service\System\UserService;


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

}