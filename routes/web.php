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
		//项目首页
		$api->get('/main','Main\IndexController@index');
		//上传身份证
		$api->post('/identify_auth','Auth\AuthController@identifyAuth');
		//上传驾驶证
		$api->post('/licence_auth','Auth\AuthController@licenceAuth');
		//上传行驶证
		$api->post('/vehicle_auth','Auth\AuthController@vehicleAuth');
		//获取上下班路线
		$api->get('/getRoute','System\DriverController@getRoute');
		//设置上下班路线
		$api->post('/setRoute','System\DriverController@setRoute');
		//获取司机信息
		$api->get('/getDriverInfo','System\DriverController@getDriverInfo');
		//获取我的车辆
		$api->get('/getMyVehicle','System\DriverController@getVehicleInfo');
		//更改行驶车辆
		$api->get('/changeVehicle','System\DriverController@changeVehicle');
		//获取用户路线
		$api->get('/getPassengerRoute','System\DriverController@getPassengerRoute');
		//开启监听模式
		$api->get('/openListen','System\DriverController@openListen');
		//获取订单信息
		$api->get('/getOrder','System\DriverController@getOrder');
		//下单
		$api->get('/bookOrder','System\DriverController@bookOrder');
		//获取订单详情
		$api->get('/getOrderDetail','System\DriverController@getOrderDetail');
	});
});


//乘客端
$api->version('passenger', ['namespace' => 'App\Http\Controllers\Passenger'], function($api){

	//登录注册
	$api->post('/register', 'System\UserController@register');
	$api->post('/send_code','System\UserController@sendMessage');
	$api->post('/login','LoginController@passengerLogin');

	$api->group(['middleware' => 'auth:passenger'], function($api){
		//获取用户信息
		$api->get('/getUserInfo','System\UserController@getUserInfo');
		//获取上下班路线
		$api->get('/getRoute','System\UserController@getRoute');
		//设置上下班路线
		$api->post('/setRoute','System\UserController@setRoute');
		//发布行程
		$api->get('/publishTrip','System\UserController@publishTrip');
		//取消订单
		$api->get('/cancelTrip','System\UserController@cancelTrip');
		//获取订单
		$api->get('/getOrder','System\UserController@getOrder');
		//获取订单详情
		$api->get('/getOrderDetail','System\UserController@getOrderDetail');
		//乘客确认上车
		$api->get('/finishOrder','System\UserController@confirmOrder');
		//获取行程列表
		$api->get('/getRouteList','System\UserController@getRouteList');
		//评价司机
		$api->get('/evaluateDriver','System\UserController@evaluateDriver');
	});

});