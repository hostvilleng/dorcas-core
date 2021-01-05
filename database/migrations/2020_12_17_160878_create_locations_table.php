<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->char('uuid',50);
		$table->integer('company_id')->unsigned();
		$table->integer('state_id')->unsigned();
		$table->char('name',80);
		$table->char('address1',100);
		$table->char('address2',100)->nullable();
		$table->char('city',80)->nullable();
		$table->timestamp('deleted_at')->nullable();
		$table->timestamps();
		$table->foreign('company_id')->references('id')->on('companies');		$table->foreign('state_id')->references('id')->on('states');
        });
    }

    public function down()
    {
        Schema::dropIfExists('locations');
    }
}