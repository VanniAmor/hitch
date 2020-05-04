<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVehicleLicenceInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 车辆退绑申请
        Schema::create('vehicle_licence_info', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增主键');
            $table->string('plate_num', 20)->comment('车牌号码');
            $table->string('vehicle_type', 10)->comment('车辆类型');
            $table->string('onwer', 10)->comment('所有人');
            $table->string('address', 50)->comment('地址');
            $table->string('VIN', 50)->comment('车辆识别代码');
            $table->string('EIN', 50)->comment('发动机识别代码');
            $table->date('reg_time')->comment('行驶证注册时间');
            $table->date('issue_time')->comment('发证时间');
            $table->string('purpose', 20)->comment('使用性质');
            $table->string('brand_model', 20)->comment('品牌型号');
            $table->string('car_brand', 20)->comment('车辆品牌');
            $table->string('color', 10)->comment('车辆颜色');
            $table->mediumInteger('did')->comment('司机ID');
            $table->timestamps();

            // 索引
            $table->index('id');
            $table->index('plate_num');
            $table->index('VIN');
            $table->index('EIN');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `vehicle_licence_info` comment '行驶证信息,车辆信息'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
