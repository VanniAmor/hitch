<?php 

namespace App\Model\Order;

use Illuminate\Database\Eloquent\Model;

class DriverServiceInfo extends Model
{
	public $table = "driver_service_info";
     public $timestamps = false;
     protected $primaryKey = 'did';
     
	/**
     * The attributes that are mass assignable.
     *
     * did    			=> 司机ID
     * total_service	=> 总服务次数
     * service_grade    => 服务平均分
     * remaining_sum    => 账户余额
     * residue_degree   => 剩余提款次数,默认有三次提现机会 
     * last_withdraw_time     => 上次提现时间
     * last_withdraw_card     => 上次提现银行卡
     * 
     */

	protected $fillable = ['did', 'total_service', 'service_grade', 'remaining_sum', 'residue_degree', 'last_withdraw_time', 'last_withdraw_card'];
}