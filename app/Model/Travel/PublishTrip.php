<?php

namespace App\Model\Travel;

use Illuminate\Database\Eloquent\Model;


class PublishTrip extends Model
{
    public $table = "trip_release";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * uid          => 用户ID
     * commute_id   => 路线ID
     * publish_time => 发布时间
     * status       => 行程状态,0-未接单，1-已接单，2-已完成
     * 
     */
    protected $fillable = [
       'uid', 'commute_id', 'publish_time', 'status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
