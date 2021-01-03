<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveGroupTypeTable extends Migration
{
    public function up()
    {
        Schema::create('leave_group_type', function (Blueprint $table) {

		$table->increments(id);
		$table->string('leave_type_id',45)->nullable()->default('NULL');
		$table->string('leave_group_id',45)->nullable()->default('NULL');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_group_type');
    }
}