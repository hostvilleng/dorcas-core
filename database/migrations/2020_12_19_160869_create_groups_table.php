<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->integer('company_id')->unsigned();
		$table->char('name',80);
		$table->text('description');
		$table->timestamps();
		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('groups');
    }
}