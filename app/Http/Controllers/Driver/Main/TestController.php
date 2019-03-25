<?php

namespace App\Http\Controllers\Driver\Main;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Model\Images\IDImage;
use Illuminate\Http\Request;
//use App\Service\ImageUploader;
use App\Jobs\ImageUploader;

class TestController extends Controller{

	public function index(Request $request){
		$driver = Auth::guard('motorman')->user();

		$front_img = $request->file('front');
		$back_img = $request->file('back');

		//图片转码
		$imageList['front'] = $this->imgToBase64( $front_img->getRealPath() );
		$imageList['back']	= $this->imgToBase64( $back_img->getRealPath() );

		$IDImage = IDImage::firstOrNew(['did' => $driver->did]);
		dispatch(new ImageUploader($IDImage,'ID',$imageList));
		/*$Uploader =  new ImageUploader($IDImage,'ID',$imageList);
		$res = $Uploader->handle();*/
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
            //$base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
            $base64_image = chunk_split(base64_encode($image_data));
            return $base64_image;
        }
        else{
            return false;
        }
	}

}