<?php 	

namespace App\Service\System;

use Illuminate\Http\Request;
use App\Model\System\User;
use App\Utils\SMS\SendTemplateSMS;
use App\Model\System\TempPhone;

class UserService{

	/**
	 * 添加用户
	 * @param Request $request [表单数据]
	 */
	public function addUser(Request $request)
	{
		//验证短信
		$res = $this->verifyCode($request);
		if($res !== true){
			return $res;
		}
		$data = array(
			'mobile' 		=> $request->input('mobile'),
			'nickname'		=> '用户' . $request->input('mobile'),
			'password'		=> password_hash($request->input('password'),PASSWORD_DEFAULT),
			'sex'			=> $request->input('sex')
		);

		if(User::where('mobile',$data['mobile'])->first())
			return array('status' => 90001, 'message' => '用户已经存在');

		//获取IP，判断所在区域
		$ip = $request->getClientIp();
		$region_num = '110000';
		$data['region_num'] = $region_num;
		return User::create($data);
	}

	/**
	 * 发送短信验证码
	 */
	public function sendCode(Request $request)
	{
		//检测用户是否存在
		$mobile = $request->input('mobile');
		$user = User::where('mobile',$mobile)->first();
		if($user){
			//用户已经存在
			return array('status' => 90001,'message' => '用户已经存在');
		}
 		//发送短信
	    $sendTemplateSMS = new SendTemplateSMS;
	    $code = mt_rand(1000,9999);
	    //短信服务已过期！！
	    //$res = $sendTemplateSMS->sendTemplateSMS($mobile, array($code, 60), 1);
	    $res = array('status' => 0, 'message' => '发送成功');

	    if($res['status'] == 0){
	    	//数据入库
	    	TempPhone::updateOrCreate(
	    		['mobile' => $mobile,],
	    		['status' => 0, 'send_time' => date('Y-m-d H-i-s'), 'code' => $code]
	    	);
	    	return array('status' => 200, 'message' => '发送成功!');
	    }
	    return array('status' => 90006, 'message' => '发送失败!');
	}


	/**
	 * 验证短信验证码
	 */
	private function verifyCode(Request $request)
	{
		$mobile = $request->input('mobile');
		$message_code = $request->input('message_code');

		$TempPhone = TempPhone::where('mobile', $mobile)->first();
		if(!$TempPhone) 
			return array('status' => 90002, 'message' => '请输入正确的手机');
	
		if($TempPhone->status == 1)
			return array('status' => 90003, 'message' => '验证码已失效');

		//15分钟内有效
		$time = strtotime('+15minutes',strtotime($TempPhone->send_time));
		if(time() >= $time )
			return array('status' => 90004, 'message' => '验证码超时');
		
		if($TempPhone->code != $message_code)
			return array('status' => 90005, 'message' => '验证码不正确');

		//更新数据库
		$TempPhone->status = 1;
		$TempPhone->save();
		return true;
	}

	public function test($data){
		return $data;
	}
}