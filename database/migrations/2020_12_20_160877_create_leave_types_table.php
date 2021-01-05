<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveTypesTable extends Migration
{
    public function up()
    {
        Schema::create('leave_types', function (Blueprint $table) {

		$table->increments('id')->unsigned();
		$table->char('uuid',36);
		$table->integer('company_id')->unsigned();
		$table->string('title');
		$table->timestamps();
		$table->timestamp('deleted_at')->nullable();
		$table->integer('approval_id')->unsigned();

        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_types');
    }
}