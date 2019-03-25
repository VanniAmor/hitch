<?php

namespace App\Model\Images;

use App\Model\Images\Images;


class VehicleImage extends Images
{
    public $table = "vehicle_img";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'did','vid','url'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
