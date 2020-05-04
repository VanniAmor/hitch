<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDriverCommuteRoute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 司机上下班路线表
        Schema::create('driver_commute_route', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增主键');
            $table->mediumInteger('did')->comment('司机ID');
            $table->tinyInteger('locale_type', 1)->comment('1-上班，2-下班');
            $table->string('origin', 20)->default('')->comment('起点');
            $table->string('origin_longitude', 25)->comment('起点百度经度');
            $table->string('origin_latitude', 25)->comment('起点百度纬度');
            $table->string('destination', 20)->default('')->comment('终点');
            $table->string('destination_longitude', 25)->comment('终点百度经度');
            $table->string('destination_latitude', 25)->comment('终点百度纬度');
            $table->date('effective_data')->nullable()->comment('驾照首次生效日期');
            $table->time('depart_time')->comment('出发时间');
            $table->time('arrive_time')->nullable()->comment('到达时间');
            $table->tinyInteger('person', 1)->comment('搭乘人数');
            $table->string('redis_key', 60)->comment('所在Redis队列中的key值');

            // 索引

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `driver_commute_route` comment '司机上下班路线表'");
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
