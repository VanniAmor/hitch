<?php

namespace App\Model\Images;

use App\Model\Images\Images;


class LicenceImage extends Images
{
    public $table = "vehicle_licence_img";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'did','uid','url'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
