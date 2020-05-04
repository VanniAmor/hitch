<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableIdentifyImg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 身份证图片
        Schema::create('identify_img', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增ID');
            $table->string('ID_number', 20)->comment('身份证号码');
            $table->string('url_front', 200)->comment('国徽面');
            $table->string('url_back', 200)->comment('照片面');
            $table->timestamps();

            // 索引
            $table->index('ID_number');

            // 数据表选项
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

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
