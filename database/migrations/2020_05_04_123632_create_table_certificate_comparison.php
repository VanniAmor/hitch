<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCertificateComparison extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 司机——行驶证关联表
        Schema::create('certificate_comparison', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->mediumInteger('did')->comment('司机ID');
            $table->mediumInteger('licence_id', 8)->comment('行驶证ID');
            $table->date('application_date')->comment('申请日期');
            $table->tinyInteger('checked', 1)->default(0)->comment('是否通过审核, 0-审核中，1-通过，-1-不通过');
            $table->tinyInteger('status', 1)->default(1)->comment('是否退绑, 1-退绑');
            $table->tinyInteger('using', 1)->default(1)->comment('0-未使用，1-正在使用');
            $table->timestamps();

            // 索引
            $table->index('did');
            $table->index('licence_id');
            $table->index('application_date');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        \DB::statement("ALTER TABLE `certificate_comparison` comment '司机——行驶证关联表'");
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
