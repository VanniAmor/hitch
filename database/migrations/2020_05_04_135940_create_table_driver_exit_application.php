<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDriverExitApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 司机退绑申请
        Schema::create('driver_exit_application', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增主键');
            $table->mediumInteger('did')->comment('司机ID');
            $table->mediumInteger('licence_id', 8)->comment('行驶证ID');
            $table->timestamp('application_time')->comment('申请时间');
            $table->timestamp('pass_time')->useCurrent()->comment('申请通过时间');
            $table->tinyInteger('status', 2)->comment('0-不通过，1-审核中，2-通过审核');

            // 索引
            $table->index('did');
            $table->index('licence_id');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `driver_exit_application` comment '司机退绑申请'");
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
