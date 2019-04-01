<?php

namespace App\Model\Licence;

use Illuminate\Database\Eloquent\Model;

//行驶证 -- 司机 关联表
class CertificateComparison extends Model
{
    public $table = "certificate_comparison";

    /**
     * The attributes that are mass assignable.
     *
     * 
     * application_date    => 申请日期
     * checked       => 是否通过审核
     * status        => 是否退绑
     * 
     */
    protected $fillable = [
        'did','licence_id','application_date','checked','status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];


}
