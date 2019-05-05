<?php

namespace App\Model\System;

use Illuminate\Database\Eloquent\Model;


class Region extends Model
{
    public $table = "region";
    public $timestamps = false;
    protected $primaryKey = 'REGION_ID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'REGION_CODE','REGION_NAME','PARENT_ID','REGION_LEVEL','REGION_ORDER','REGION_NAME_EN','REGION_SHORTNAME_EN'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
