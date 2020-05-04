<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWithdrawRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 司机提款记录
        Schema::create('withdraw_record', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增主键');
            $table->mediumInteger('did')->comment('司机ID');
            $table->decimal('count', 10, 2)->comment('提款金额');
            $table->timestamp('withdraw_send_time')->comment('提款申请时间戳');
            $table->string('withdraw_card', 30)->comment('提款银行卡');

            // 索引
            $table->index('did');
            $table->index('withdraw_card');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `withdraw_record` comment '司机提款记录'");
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
