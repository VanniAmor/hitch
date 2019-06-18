<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;


class CheckIdentify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {   
        $driver = Auth::guard('motorman')->user();
        switch ($driver->checked) {
            case 0:
                #未上传
                throw new Exception("请先上传您的身份证信息", 40001);
                break;
            case 1:
                #正在审核
                throw new Exception("您的身份证信息正在审核", 40002);
                break;
            case -1:
                throw new Exception("您的身份证信息未能通过审核,请重新上传", 40003);
                break;
        }
        switch ($driver->usable) {
            case 0:
                #未上传
                throw new Exception("请先上传您的驾驶证信息", 50001);
                break;
            case 1:
                #正在审核
                throw new Exception("您的驾驶证信息正在审核", 50002);
                break;
            case -1:
                throw new Exception("您的驾驶证信息未能通过审核,请重新上传", 50003);
                break;
        }
        return $next($request);
    }
}
