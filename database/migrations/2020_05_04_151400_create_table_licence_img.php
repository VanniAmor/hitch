<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLicenceImg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 驾驶证图片
        Schema::create('identify_img', function (Blueprint $table) {
            $table->mediumIncrements('id')->comment('自增ID');
            $table->mmediumIntegeredium('did')->comment('司机ID');
            $table->string('url', 200)->comment('图片url');
            $table->timestamps();

            // 索引
            $table->index('did');

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
