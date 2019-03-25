<?php

namespace App\Jobs;

//引入七牛云SDK
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use App\Model\Images\Images;
use Illuminate\Support\Facades\Auth as SysAuth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;


//图片上传类
class ImageUploader implements ShouldQueue
{
    use InteractsWithQueue, Queueable;
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
    public function __construct(Images $imagesORM ,$type, $imageList)
    {   
        //实例化鉴权类
     	$this->auth = new Auth($this->ACCESS_KEY, $this->SECRET_KEY);
     	$this->uploader = new UploadManager();

     	//Eloquent
     	$this->driver = SysAuth::guard('motorman')->user();
     	$this->images = $imagesORM;
     	$this->type = $type;

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
        		$this->uploadIDImage();
        		break;
        	
        	case 'LICENCE':
        		$this->uploadLicence();
        		break;

        	case 'VEHICLE':
        		$this->uploadVehicle();
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
        $this->images->did = $this->driver->did;
        $host = 'http://' . env('QINIU_HOST') . '/';

        $this->images->url_front = $host . $res['front'][0]['key'];
        $this->images->url_back = $host . $res['back'][0]['key'];
        $this->images->save();

        //删除本地图片
        $this->RemoveImage($filePath['front']);
        $this->RemoveImage($filePath['back']);
    }

    //上传驾驶证图片
    private function uploadLicenceImage()
    {
        //生成上传token
        $token = $this->auth->uploadToken($this->BUCKET_ID);

        // 要上传文件的本地路径
        $filePath = $this->TransformImage($this->imageList);

        print_r($filePath);

        //上传后文件名称
        $key = $this->dirver->file_num . '_drivingLicence.jpg';

        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        $res = $this->uploader->putFile($token,$key,$filePath);

        //数据入库
        $this->images->did = $this->driver->did;
        //还不清楚返回的数据结构
        $this->images->url = $res;
        $this->images->save();

        //删除图片
        $this->unlink($filePath);
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

}
