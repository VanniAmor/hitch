<?php

namespace App\Model\Licence;

use Illuminate\Database\Eloquent\Model;

class VehicleLicence extends Model
{
    public $table = "vehicle_licence_info";

    /**
     * The attributes that are mass assignable.
     *
     * plate_num    => 车牌号码
     * vehicle_type => 车辆类型
     * owner        => 所有人
     * address      => 住址
     * VIN          => 车辆识别代码 
     * EIN          => 发动机识别代码
     * reg_time     => 注册日期
     * issue_time   => 发证日期
     * purpose      => 使用性质
     * brand_model  => 品牌信号
     * car_brand    => 车辆品牌
     * color        => 车辆颜色
     * 
     */
    protected $fillable = [
        'plate_number','vehicle_type','owner','address','VIN','EIN','reg_time','issue_time','purpose','brand_model','car_brand','color'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
