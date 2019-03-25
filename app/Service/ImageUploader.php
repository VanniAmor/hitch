<?php

namespace App\Service;

//引入七牛云SDK
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Config;
use App\Model\Images\Images;
use Illuminate\Support\Facades\Auth as SysAuth;

//图片上传类
class ImageUploader
{
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

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Images $images ,$type, $imageList)
    {
        //实例化鉴权类
     	$this->auth = new Auth($this->ACCESS_KEY, $this->SECRET_KEY);
     	$this->uploader = new UploadManager();

     	//Eloquent
     	$this->driver = SysAuth::guard('motorman')->user();
     	$this->images = $images;
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
		$res['front'] = $this->uploader->putFile($token, $key['front'], $filePath['front']);
		$res['back'] = $this->uploader->putFile($token, $key['back'], $filePath['back']);

		//信息入库
        $this->images->did = $this->driver->did;
        $host = 'http://' . env('QINIU_HOST') . '/';
        $this->images->front_url = $host . $res['front'];
        $this->images->back_url = $host . $res['back'];
        $this->images->save();

		//删除本地图片
		$this->unlink($filePath['front']);
        $this->unlink($filePath['back']);
    }

    /**
     * 生成图片
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
            $filename = getcwd() . '/images/' . $imageName;
    		$res = file_put_contents($filename, base64_decode($images));
            var_dump($res);
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