<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDriverServiceInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 司机服务信息表
         Schema::create('driver_service_info', function (Blueprint $table) {
            $table->mediumInteger('did')->comment('司机ID');
            $table->mediumInteger('total_service')->comment('总服务次数');
            $table->double('service_grade', 10, 2)->default('4.00')->comment('司机服务评分,满分5分');
            $table->decimal('remaining_sum', 10, 2)->default('0.00')->comment('司机余额');
            $table->tinyInteger('residue_degree', 1)->comment('剩余提款次数');
            $table->timestamp('last_withdraw_time')->comment('上次提款时间');
            $table->string('last_withdraw_card', 30)->comment('上次提现银行卡');
            $table->timestamps();

            // 索引
            $table->index('did');
            $table->index('last_withdraw_card');
            $table->primary('did');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `driver_service_info` comment '司机服务信息表'");
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
