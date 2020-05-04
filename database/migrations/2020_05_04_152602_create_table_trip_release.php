<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTripRelease extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 上下班行程发布表
        Schema::create('trip_release', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增主键');
            $table->mediumInteger('uid')->comment('用户ID');
            $table->mediumInteger('commute_id')->comment('路线ID');
            $table->timestamp('publish_time')->comment('发起时间');
            $table->tinyInteger('status', 1)->comment('0-未接单，1-已接单，2-已完成，-1取消');

            // 索引
            $table->index('uid');
            $table->index('commute_id');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `trip_release` comment '上下班行程发布表'");
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
