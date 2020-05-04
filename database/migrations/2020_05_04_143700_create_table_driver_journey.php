<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDriverJourney extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         // 司机发布的远途行程
         Schema::create('driver_journey', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增主键');
            $table->mediumInteger('did')->comment('司机ID');
            $table->string('origin', 50)->default('')->comment('起点');
            $table->string('origin', 50)->default('')->comment('终点');
            $table->tinyInteger('person', 1)->comment('搭乘人数');
            $table->dateTime('publish_time')->comment('发布时间');
            $table->dateTime('depart_time')->nullable()->comment('出发时间');
            $table->timestamps();

            // 索引
            $table->index('did');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `driver_journey` comment '司机发布的远途行程'");
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
