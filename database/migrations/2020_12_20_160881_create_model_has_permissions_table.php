<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelHasPermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('model_has_permissions', function (Blueprint $table) {

		$table->integer('permission_id')->unsigned();
		$table->integer('model_id')->unsigned();
		$table->char('model_type',50);
		$table->primary(['permission_id','model_id','model_type']);
		$table->foreign('permission_id')->references('id')->on('permissions');
        });
    }

    public function down()
    {
        Schema::dropIfExists('model_has_permissions');
    }
}