<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersPrefixTable extends Migration
{
    public function up()
    {
        Schema::create('users_prefix', function (Blueprint $table) {

		$table->integer('id',);
		$table->char('user_id',80)->default('');
		$table->string('prefix',25);
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('users_prefix');
    }
}