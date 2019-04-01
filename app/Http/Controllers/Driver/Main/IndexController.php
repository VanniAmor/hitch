<?php

namespace App\Http\Controllers\Driver\Main;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Response;

class IndexController extends Controller{

	public function index(){
		$user = Auth::guard('motorman')->user();
		return $user;
	}

}