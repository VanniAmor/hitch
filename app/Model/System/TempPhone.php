<?php

namespace App\Model\System;

use Illuminate\Database\Eloquent\Model;


class TempPhone extends Model
{
    public $table = "sms_verify";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mobile','code','status','send_time'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
