<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('leave_groups', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->integer('group_id')->unsigned();
		$table->enum('group_type',['team','department']);
		$table->string('duration_days');
		$table->enum('duration_term',['annual']);
		$table->timestamps();
		$table->integer('company_id')->unsigned();
		$table->timestamp('deleted_at')->nullable();
		$table->char('uuid',36);

        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_groups');
    }
}