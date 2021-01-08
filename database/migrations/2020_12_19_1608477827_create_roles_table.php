<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',36);
		$table->string('name');
		$table->string('guard_name');
		$table->text('extra_json');
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
}