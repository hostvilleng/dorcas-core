<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelHasRolesTable extends Migration
{
    public function up()
    {
        Schema::create('model_has_roles', function (Blueprint $table) {

		$table->integer('role_id',)->unsigned();
		$table->integer('model_id',)->unsigned();
		$table->char('model_type',50);
		$table->primary(['role_id','model_id','model_type']);
		$table->foreign('role_id')->references('id')->on('roles');
        });
    }

    public function down()
    {
        Schema::dropIfExists('model_has_roles');
    }
}