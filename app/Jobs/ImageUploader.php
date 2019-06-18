<?php

namespace App\Jobs;

//引入七牛云SDK
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use App\Model\Images\IDImage;
use App\Model\Images\DrivingLicenceImage;
use App\Model\Images\VehicleLicenceImage;
use Illuminate\Support\Facades\Auth as SysAuth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;


//图片上传类
/*class ImageUploader implements ShouldQueue*/
class ImageUploader extends Job
{
   /* use InteractsWithQueue, Queueable;*/
	protected $ACCESS_KEY = 'YBEYWaqwXhGNpNIuEOsuSyLtQ0i0b34gobjSYsKL';
	protected $SECRET_KEY = '3BCpdcLZZ3wXw-l5psmu-D8RHjnmmlJnciUTeVuK';
	protected $BUCKET_ID  = 'images';
	/*const BUCKET_LICENCE = 'Licence';
	const BUCKET_VEHICLE = 'Vehicle';*/

	protected $auth;
	protected $uploader;

	protected $images;
	private $driver;
	private $type;
	private $imageList;

    //失败重试次数
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($imageID ,$type, $imageList)
    {   
        //实例化鉴权类
     	$this->auth = new Auth($this->ACCESS_KEY, $this->SECRET_KEY);
     	$this->uploader = new UploadManager();

     	//Eloquent
     	$this->driver = SysAuth::guard('motorman')->user();
     	$this->type = $type;
        $this->images = $imageID;
     	$this->imageList = $imageList;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        //上传ID照片
        switch ($this->type) {
        	case 'ID':
                $this->images = IDImage::find($this->images);
        		$this->uploadIDImage();
        		break;
        	case 'LICENCE':
                $this->images = DrivingLicenceImage::find($this->images);
        		$this->uploadLicenceImage();
        		break;
        	case 'VEHICLE_LICENCE':
                $this->images = VehicleLicenceImage::find($this->images);
        		$this->uploadVehicleLicenceImage();
        		break;
        	default:
        		return false;
        		break;
        }
    }

    //上传身份证图片
    private function uploadIDImage()
    {
    	//生成上传token
    	$token = $this->auth->uploadToken($this->BUCKET_ID);

    	// 要上传文件的本地路径
		$filePath = $this->TransformImage($this->imageList);

		//上传后保存的名称
		$key = array(
			'front' => $this->driver->ID_number . '_ID_front.jpg',
			'back'  => $this->driver->ID_number . '_ID_back.jpg'
		);

		// 调用 UploadManager 的 putFile 方法进行文件的上传。
		$res['front'] = $this->uploader->putFile($token,$key['front'],$filePath['front']);
		$res['back'] = $this->uploader->putFile($token,$key['back'],$filePath['back']);

		//信息入库
        $host = 'http://' . env('QINIU_HOST') . '/';

        $this->images->url_front = $host . $key['front'];
        $this->images->url_back = $host . $key['back'];
        $this->images->save();

        //删除本地图片
        $this->RemoveImage($filePath['front']);
        $this->RemoveImage($filePath['back']);

        //通知管理员
        $this->publishVerify(1);
    }

    //上传驾驶证图片
    private function uploadLicenceImage()
    {
        //生成上传token
        $token = $this->auth->uploadToken($this->BUCKET_ID);

        // 要上传文件的本地路径
        $filePath = $this->TransformImage($this->imageList);

        //上传后文件名称
        $key = $this->driver->file_num . '_drivingLicence.jpg';

        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        $res = $this->uploader->putFile($token,$key,$filePath);

        //数据入库
        $host = 'http://' . env('QINIU_HOST') . '/';
        $this->images->url = $host . $key;
        $this->images->save();

        //删除图片
        $this->RemoveImage($filePath);

        //通知管理员
        $this->publishVerify(2);
    }


    //上传行驶证图片
    private function uploadVehicleLicenceImage()
    {
        //生成上传token
        $token = $this->auth->uploadToken($this->BUCKET_ID);

        // 要上传文件的本地路径
        $filePath = $this->TransformImage($this->imageList);

        //上传后文件名称
        $key = array(
            'vehicle_licence' => $this->images->vid . '_vehicleLicence.jpg',
            'vehicle'  => $this->images->vid . '_vehicle.jpg'
        );

        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        $res = array(
            'vehicle_licence' => $this->uploader->putFile($token,$key['vehicle_licence'],$filePath['vehicle_licence_img']),
            'vehicle' => $this->uploader->putFile($token,$key['vehicle'],$filePath['vehicle_img']),
        );

        //数据入库
        $host = 'http://' . env('QINIU_HOST') . '/';
        $this->images->licence_img_url = $host . $key['vehicle_licence'];
        $this->images->vehicle_img_url = $host . $key['vehicle'];
        $this->images->updated_at = date('Y-m-d H:i:s');
        $this->images->save();

        //删除本地图片
        $this->RemoveImage($filePath['vehicle_licence_img']);
        $this->RemoveImage($filePath['vehicle_img']);

        //通知管理员
        $this->publishVerify(3);
    }

    /**
     * Base64转图片
     */
    private function TransformImage($images)
    {   
    	if(is_array($images)){
    		$res = array();
    		foreach ($images as $key => $value) {
    			$res[$key] = $this->TransformImage($value);
    		}
    		return $res;
    	}else{
    		$imageName = date("His",time())."_".rand(1111,9999).'.jpg';
    		$filename = getcwd() . '/public/images/' . $imageName;
    		$res = file_put_contents($filename, base64_decode($images));
    		return $res ? $filename : false;
    	}
    }

    /**
     * 删除图片
     */
   	private function RemoveImage($filename)
   	{   
   		unlink($filename);
   	}


    private function publishVerify($type)
    {
        //循环获取Redis队列的中司机信息,并推送信息
        $url = 'http://' . env('GO_REST_HOST') . '/publish';

        $data = array(
            'appkey'    => env('GO_COMMON_KEY'),
            'channel'   => 'Verify',
            'content'   => $type
        );
        //发送推送请求
        $res = $this->posturl($url, $data);
    }

    private function posturl($url,$data){
        $ch = curl_init();
        curl_setopt ( $ch, CURLOPT_URL, $url );//地址
        curl_setopt ( $ch, CURLOPT_POST, 1 );//请求方式为post
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );//不打印header信息
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );//返回结果转成字符串
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );//post传输的数据。
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output,true);
    }
}
