<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // UserInfo
        Schema::create('user', function (Blueprint $table) {
            $table->mediumIncrements('uid')->comment('司机ID');
            $table->string('mobile', 20);
            $table->string('ID_number', 20)->comment('身份证号码');
            $table->string('password', 200);
            $table->tinyInteger('sex', 1)->comment('1-男；2-女');
            $table->string('realname', 10)->nullable()->comment('真实姓名');
            $table->tinyInteger('age', 2)->comment('年龄');
            $table->date('birthday')->nullable->comment('出生日期');
            $table->string('address', 100)->default('')->comment('详细居住地址');
            $table->string('nickname', 10)->default('')->comment('昵称');
            $table->string('issue_authority', 50)->comment('身份证签发机关');
            $table->string('region_num', 10)->comment('区域ID');
            $table->tinyInteger('type', 1)->default('0')->comment('1为手机注册，2微信注册');
            $table->tinyInteger('wx_status', 1)->default('0')->comment('是否使用微信');
            $table->tinyInteger('checked', 1)->default('0')->comment('是否通过审核，0-未上传，1-审核中，2-通过，-1不通过');
            $table->string('remember_token', 200)->nullable()->comment('');

            // 索引
            $table->index('uid');
            $table->index('ID_number');
            $table->index('mobile');
            

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `user` comment '用户信息表'");
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
