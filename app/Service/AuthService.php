<?php

namespace App\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\ImageUploader;
use App\Jobs\TestJob;
use App\Model\Images\IDImage;
use App\Model\Images\LicenceImage;
use App\Model\Images\VehicleImage;


class AuthService
{
	//use DispatchesJobs;

	protected $MAX_SIZE = 4 * 1024 * 1024;

	protected $client;
	protected $driver;

	public function __construct(){
		$this->client = new \AipOcr(env('BAIDU_APPID'),env('BAIDU_APIKEY'),env('BAIDU_SECRET_KEY'));
		$this->driver = Auth::guard('motorman')->user();
	} 

	/**
	 * 司机真实身份认证
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function identifyAuth(Request $request)
	{
		$front_img = urldecode( $request->input('front') );
		$back_img = urldecode($request->input('back') );
		
		//去除baase64头部,并urlencode编码
		//若使用百度的SDK,不需要手动进行urlencode编码
		$front_img = substr(strstr($front_img,','), 1);
		$back_img =  substr(strstr($back_img,','), 1);
		$imageList = ['front' => $front_img, 'back' => $back_img];

		//接口可选参数
		$options = array();
		$options["detect_direction"] = "true";
		$options["detect_risk"] = "false";

		//发送验证
		//$front_res = $this->client->idcard($front_img,'front',$options);
		//$back_res = $this->client->idcard($back_img,'back',$options);

		//通过验证,身份信息入库
		//$this->handleIDAuthRes($front_res,$back_res);

		//图片保存
		$IDImage = IDImage::firstOrNew(['did' => $this->driver->did]);

		//dispatch(new TestJob('Hello World'));

		dispatch(new ImageUploader($IDImage,'ID',$imageList));

		return true;
	}


	/**
	 * 司机驾驶证验证
	 */
	public function licenceAuth(Request $request)
	{
		$licence_img = urldecode( $request->input('licence') );

		//去除baase64头部,并urlencode编码
		//若使用百度的SDK,不需要手动进行urlencode编码
		$licence_img = substr(strstr($licence_img,','), 1);

		//接口可选参数
		$options = array();
		$options["detect_direction"] = "true";
		$options["detect_risk"] = "false";

		// 发送验证
		$licence_res = $this->client->drivingLicense($image, $options);

		// 通过验证,信息入库
		$this->handleLicenceAuthRes($licence_res);

		//图片保存
		$LicenceImage = LicenceImage::firstOrNew(['did' => $this->driver->did]);

		$this->dispatch(new ImageUploader($LicenceImage,'LICENCE',$$licence_img));

		return true;
	}


	/**
	 * 图片base64编码
	 */
	private function imgToBase64($ImageFile)
	{
		if(file_exists($ImageFile) || is_file($ImageFile)){
            $base64_image = '';
            $image_info = getimagesize($ImageFile);
            $image_data = fread(fopen($ImageFile, 'r'), filesize($ImageFile));
            $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
            return $base64_image;
        }
        else{
            return false;
        }
	}

	/**
	 * 处理身份验证信息
	 * 信息入库
	 */
	private function handleIDAuthRes($front_res,$back_res)
	{	
		$front_data = $front_res['words_result'];
		$back_data = $back_res['words_result'];

		//身份证号码
		$this->driver->ID_number = $front_data['公民身份号码']['words'];
		//真实名称
		$this->driver->realname = $front_data['姓名']['words'];
		//出生日期
		//date_create_from_format('Ymd',$date);
		$this->driver->birthday = date_create_from_format('Ymd',$front_data['出生']['words']);
		//地址		
		$this->driver->address = $front_data['住址']['words'];
		//身份证签发机关
		$this->driver->issue_authority = $back_data['签发机关']['words'];

		//信息入库
		return $this->driver->save();
	}


	/**
	 * 处理驾驶证验证信息
	 * 信息入库
	 */
	private function handleLicenceAuthRes($licence_res)
	{
		$licence_data = $licence_res['words_result'];

		//证号
		$this->driver->file_num = $licence_data['证号']['words'];
		//初次发证日期
		$this->driver->first_issue = date_create_from_format('Ymd',$licence_data['初次领证日期']['words']);
		//初次生效日期
		$this->driver->effective_data = date_create_from_format('Ymd',$licence_data['有效起始日期']['words']);
		//有效期限
		$this->driver->indate = $licence_data['有效期限']['words'];
		//驾驶证类型
		$this->driver->motocycle_type = $licence_data['准驾车型']['words'];
		//是否可用
		$this->driver->usable = 1;

		//信息入库
		return $this->driver->save();
	}

}