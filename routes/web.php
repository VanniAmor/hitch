<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
use Symfony\Component\HttpFoundation\Cookie;


$router->get('/', function () use ($router) {
    return $router->app->version();
});


//Dingo API 路由
$api = app('Dingo\Api\Routing\Router');

//司机端
$api->version('driver', ['namespace' => 'App\Http\Controllers\Driver'], function($api){
	//登录注册
	$api->post('/register', 'System\DriverController@register');
	$api->post('/send_code','System\DriverController@sendMessage');
	$api->post('/login','LoginController@driverLogin');

	$api->get('/redis',function(){
		app('redis')->set('lumen', 'Hello, Lumen.');
		return app('redis')->get("lumen");
	});
	$api->get('/refresh','LoginController@driverRefresh');


	//测试
	$api->post('/imgclassify','Main\TestController@imgClassify');

	//登录拦截
	$api->group(['middleware' => 'auth:motorman'], function($api){
		//Token刷新
		
		//项目首页
		$api->get('/main','Main\IndexController@index');
		//上传身份证
		$api->post('/identify_auth','Auth\AuthController@identifyAuth');
		//上传驾驶证
		$api->post('/licence_auth','Auth\AuthController@licenceAuth');
		//上传行驶证
		$api->post('/vehicle_auth','Auth\AuthController@vehicleAuth');
		//获取上下班路线
		$api->get('/getRoute',function(){
			return response($_SERVER['HTTP_ORIGIN']);
		});
		//获取司机信息
		$api->get('/getDriverInfo','System\DriverController@getDriverInfo');
		//获取我的车辆
		$api->get('/getMyVehicle','System\DriverController@getVehicleInfo');


	});
});


//乘客端
$api->version('passenger', ['namespace' => 'App\Http\Controllers\Passenger'], function($api){

	//登录注册
	$api->post('/register', 'System\UserController@register');
	$api->post('/send_code','System\UserController@sendMessage');
	$api->post('/login','LoginController@passengerLogin');


	$api->group(['middleware' => 'auth:passenger'], function($api){
		//项目首页
		$api->get('/main','Main\IndexController@index');
		


	});

});