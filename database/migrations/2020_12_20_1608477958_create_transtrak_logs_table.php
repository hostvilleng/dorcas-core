<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranstrakLogsTable extends Migration
{
    public function up()
    {
        Schema::create('transtrak_logs', function (Blueprint $table) {

		$table->increments('id');
		$table->string('payload',45);
		$table->tinyInteger('passed')->default('0');
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('transtrak_logs');
    }
}