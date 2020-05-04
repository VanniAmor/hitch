<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVehicleLicenceImg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 驾驶证图片
        Schema::create('vehicle_licence_img', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增ID');
            $table->mmediumIntegeredium('vid')->comment('行驶证ID');
            $table->string('licence_img_url', 200)->comment('行驶证图片URL');
            $table->string('vehicle_img_url', 200)->comment('车辆图片URL');
            $table->timestamps();

            // 索引
            $table->index('vid');


            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });
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
