<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeTeamTable extends Migration
{
    public function up()
    {
        Schema::create('employee_team', function (Blueprint $table) {

		$table->integer('employee_id')->unsigned();
		$table->integer('team_id')->unsigned();
		$table->primary(['employee_id','team_id']);
        $table->foreign('employee_id')->references('id')->on('employees');
        $table->foreign('team_id')->references('id')->on('teams');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_team');
    }
}