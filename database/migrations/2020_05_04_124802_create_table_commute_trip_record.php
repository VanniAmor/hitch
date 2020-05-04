<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCommuteTripRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 上下班行程记录表
        Schema::create('commute_trip_record', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->mediumInteger('release_id')->comment('行程发布表ID');
            $table->mediumInteger('driver_commute_id')->default(0)->comment('关联的司机出行路线');
            $table->mediumInteger('did')->comment('司机ID');
            $table->double('grade', 10, 2)->default('4.00')->comment('评分');
            $table->decimal('count', 10, 2)->comment('金额');
            $table->dateTime('depart_time')->comment('确认上车时间');
            $table->tinyInteger('status', 1)->default(1)->comment('0-未完成，1-已完成，-1取消');
            $table->string('distance', 10)->comment('行驶距离，单位：公里');
            $table->timestamps();

            // 索引
            $table->index('did');
            $table->index('release_id');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `commute_trip_record` comment '上下班行程记录表'");
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
