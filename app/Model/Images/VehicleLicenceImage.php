<?php

namespace App\Model\Images;

use App\Model\Images\Images;

//行驶证 + 车辆 图片
class VehicleLicenceImage extends Images
{
    public $table = "vehicle_licence_img";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vid','licence_img_url','vehicle_img_url'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
