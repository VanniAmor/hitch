<?php

namespace App\Model\Images;

use App\Model\Images\Images;


class IDImage extends Images
{
    public $table = "identify_img";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'did','url_back','url_front'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
