<?php

namespace App\Model\Images;

use App\Model\Images\Images;

//驾驶证图片
class DrivingLicenceImage extends Images
{
    public $table = "licence_img";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'did','url'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
