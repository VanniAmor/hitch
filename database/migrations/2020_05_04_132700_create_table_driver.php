<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDriver extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 司机信息
        Schema::create('driver', function (Blueprint $table) {
            $table->mediumIncrements('did')->comment('司机ID');
            $table->string('password', 200);
            $table->string('mobile', 20);
            $table->tinyInteger('sex', 1)->comment('1-男；2-女');
            $table->tinyInteger('age', 2)->comment('年龄');
            $table->string('ID_number', 20)->comment('身份证号码');
            $table->string('realname', 10)->nullable()->comment('真实姓名');
            $table->date('birthday')->nullable->comment('出生日期');
            $table->string('file_num', 50)->nullable()->comment('驾驶证档案编号');
            $table->date('first_issue')->nullable()->commnet('驾驶执照初次领证日期');
            $table->string('motocycle_type', 20)->nullable()->comment('驾驶证类型');
            $table->date('indate')->nullable()->comment('驾驶证有效期');
            $table->date('effective_data')->nullable()->comment('驾照首次生效日期');
            $table->string('address', 100)->default('')->comment('详细居住地址');
            $table->string('issue_authority', 20)->default('')->comment('身份证签发机关');
            $table->tinyInteger('usable', 1)->default('0')->comment('驾驶证是否可用，0-未上传，1-审核中，2-通过，-1不通过');
            $table->tinyInteger('checked', 1)->default('0')->comment('是否通过审核，0-未上传，1-审核中，2-通过，-1不通过');
            $table->tinyInteger('status', 1)->default('0')->comment('是否删除');
            $table->string('remember_token', 200)->nullable()->comment('');

            $table->timestamps();

            // 索引
            $table->index('mobile');
            $table->index('ID_number');
            $table->index('file_num');
            $table->index('first_issue');
            $table->index('effective_data');
            $table->index('issue_authority');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `driver` comment '司机信息'");
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
