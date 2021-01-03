<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatesTable extends Migration
{
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->char('uuid',50);
		$table->integer('country_id',)->unsigned();
		$table->char('name',80);
		$table->char('iso_code',5)->nullable()->default('NULL');
		$table->timestamp('deleted_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary('id');
		$table->foreign('country_id')->references('id')->on('countries');
        });
    }

    public function down()
    {
        Schema::dropIfExists('states');
    }
}