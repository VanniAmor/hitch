<?php

namespace App\Http\Controllers\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Service\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Model\Images\DrivingLicenceImage;
use App\Model\Images\VehicleLicenceImage;
use App\Model\Licence\VehicleLicence;
use App\Model\Licence\CertificateComparison;
use Illuminate\Support\Facades\DB; 
/*use App\Jobs\ImageUploader;*/
use App\Service\ImageUploader;

class AuthController extends Controller
{
	protected $authService;

	public function __construct(AuthService $authService){
		$this->authService = $authService;
	}

	/**
	 * 司机真实身份验证
	 */
	public function identifyAuth(Request $request)
	{	
		$res = $this->authService->identifyAuth($request);
		return response()->json($res);
	}

	/**
	 * 司机驾驶证验证
	 */
	public function licenceAuth(Request $request)
	{
		$res = $this->authService->licenceAuth($request);
		return response()->json($res);
	}


	/**
	 * 车辆行驶证验证
	 */
	public function vehicleAuth(Request $request)
	{	
		$res = $this->authService->vehicleAuth($request);
		return response()->json($res);
	}

}
