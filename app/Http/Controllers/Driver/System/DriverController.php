<?php

namespace App\Http\Controllers\Driver\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Service\System\DriverService;


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

}