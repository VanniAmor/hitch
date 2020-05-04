<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 支付记录
        Schema::create('payment', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增主键');
            $table->mediumInteger('uid')->comment('用户ID');
            $table->tinyInteger('type', 1)->comment('支付方式0-支付宝，1-微信');
            $table->string('sequence_id', 50)->default('')->comment('第三方流水号');
            $table->decimal('count', 10, 2)->comment('总支付金额');
            $table->mediumInteger('payee')->comment('收款人');
            $table->timestamp('publish_time')->comment('发起时间');
            $table->timestamp('pay_time')->comment('支付时间');
            $table->tinyInteger('is_pay', 1)->comment('是否支付0-否，1-是');
            $table->tinyInteger('person', 1)->comment('搭乘人数');

            // 索引
            $table->index('uid');
            $table->index('payee');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `payment` comment '支付记录'");
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
