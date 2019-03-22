<?php

namespace App\Http\Controllers\Driver\Main;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class IndexController extends Controller{

	public function index(){
		$user = Auth::guard('motorman')->user();
		var_dump($user);
	}

}