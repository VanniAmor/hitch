<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAdminUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 创建 后台管理员表
        Schema::create('admin_user', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 20)->default('')->comment('用户登录名');
            $table->string('password', 150)->default('')->comment('用户密码');
            $table->tinyInteger('level', 1)->default(0)->comment('管理员级别,0-普通管理员，1-超级管理员');
            $table->string('remember_token', 200)->nullable()->comment('');
            $table->string('avatar', 200)->nullable()->comment('用户头像');
            $table->timestamps();

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `admin_user` comment '后台管理员表'");
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
