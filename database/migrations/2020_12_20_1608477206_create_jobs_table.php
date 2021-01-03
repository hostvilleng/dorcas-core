<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->string('queue');
		;
		$table->integer('attempts',)->unsigned();
		$table->integer('reserved_at',)->unsigned()->nullable()->default('NULL');
		$table->integer('available_at',)->unsigned();
		$table->integer('created_at',)->unsigned();
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}