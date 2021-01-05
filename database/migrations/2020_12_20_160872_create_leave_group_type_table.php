<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveGroupTypeTable extends Migration
{
    public function up()
    {
        Schema::create('leave_group_type', function (Blueprint $table) {

		$table->integer('id')->primary();
		$table->string('leave_type_id',45)->nullable();
		$table->string('leave_group_id',45)->nullable();
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_group_type');
    }
}