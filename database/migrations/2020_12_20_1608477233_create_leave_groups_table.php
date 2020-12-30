<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('leave_groups', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->bigInteger('group_id',)->unsigned();
		$table->enum('group_type',['team','department']);
		$table->string('duration_days');
		$table->enum('duration_term',['annual']);
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->integer('company_id',)->unsigned();
		$table->timestamp('deleted_at')->nullable()->default('NULL');
		$table->char('uuid',36);
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_groups');
    }
}