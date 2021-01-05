<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->char('uuid',36);
		$table->string('name');
		$table->string('guard_name');
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}