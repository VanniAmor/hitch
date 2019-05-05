<?php

namespace App\Model\Travel;

use Illuminate\Database\Eloquent\Model;


class TripRecord extends Model
{
    public $table = "commute_trip_record";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * release_id       => 行程发布表ID
     * did              => 司机ID
     * grade            => 该次评分
     * count            => 金额
     * depart_time      => 确认上车时间
     * status           => 0-未完成, 1-已完成
     * driver_commute_id => 关联的司机路线ID
     * 
     */
    protected $fillable = [
       'release_id', 'did', 'grade', 'count', 'depart_time', 'status', 'driver_commute_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
