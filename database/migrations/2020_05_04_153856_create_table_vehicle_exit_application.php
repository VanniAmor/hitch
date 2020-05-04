<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVehicleExitApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 车辆退绑申请
        Schema::create('vehicle_exit_application', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增主键');
            $table->mediumInteger('did')->comment('司机ID');
            $table->mediumInteger('vehicle_id', 8)->comment('车辆ID');
            $table->string('plate_num', 20)->comment('车牌号码');
            $table->timestamp('application_time')->comment('申请时间');
            $table->timestamp('pass_time')->useCurrent()->comment('申请通过时间');
            $table->tinyInteger('status', 2)->comment('0-不通过，1-审核中，2-通过审核');

            // 索引
            $table->index('did');
            $table->index('vehicle_id');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `vehicle_exit_application` comment '车辆退绑申请'");
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
