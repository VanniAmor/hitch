<?php

namespace App\Model\Travel;

use Illuminate\Database\Eloquent\Model;


class DriverRoute extends Model
{
    public $table = "driver_commute_route";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * did          => 司机ID
     * locale_type  => 路线类型
     * origin       => 起点
     * origin_longitude  => 起点百度经度
     * origin_latitude   => 起点百度纬度
     * destination  => 终点
     * destination_longitude  => 终点百度经度
     * destination_latitude   => 终点百度纬度
     * depart_time  => 出发时间
     * arrive_time  => 到达时间
     * person       => 搭乘人数
     * redis_key    => 所在redis队列中的key
     * 
     */
    protected $fillable = [
       'did', 'locale_type', 'origin', 'origin_longitude', 'origin_latitude', 'destination',  'destination_longitude', 'destination_latitude', 'depart_time', 'arrive_time','person','redis_key'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
