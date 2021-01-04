<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleHasPermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('role_has_permissions', function (Blueprint $table) {

		$table->integer('permission_id')->unsigned();
		$table->integer('role_id')->unsigned();
		$table->primary(['permission_id','role_id']);
		$table->foreign('permission_id')->references('id')->on('permissions');		$table->foreign('role_id')->references('id')->on('roles');
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_has_permissions');
    }
}