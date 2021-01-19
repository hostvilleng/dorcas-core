<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {

            $table->increments('id');
            $table->string('queue');
            $table->longText('payload');
            $table->integer('attempts')->unsigned();
            $table->integer('reserved_at')->unsigned()->nullable();
            $table->integer('available_at')->unsigned();
            $table->integer('created_at')->unsigned();
            $table->longText('payload')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}