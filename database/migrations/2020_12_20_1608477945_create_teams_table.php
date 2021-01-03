<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->char('uuid',50);
		$table->integer('company_id',)->unsigned();
		$table->char('name',80);
		$table->text('description');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->nullable()->default('CURRENT_TIMESTAMP');
		$table->primary('id');
		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('teams');
    }
}