<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSmsVerify extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 短信验证表
        Schema::create('sms_verify', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增主键');
            $table->string('mobile', 20)->default('')->comment('手机号码');
            $table->char('code', 4)->comment('验证码');
            $table->tinyInteger('status', 1)->comment('验证码状态，0为未使用，1为已使用');
            $table->timestamp('send_time')->comment('发起时间');
            // 索引
            $table->index('mobile');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `sms_verify` comment '短信验证表'");
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
