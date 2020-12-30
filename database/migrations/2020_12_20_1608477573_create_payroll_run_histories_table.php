<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollRunHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_run_histories', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->integer('employee_id',)->unsigned();
		$table->integer('run_id',)->unsigned();
		$table->enum('status',['draft','approved','processed']);
		$table->json('status_data')->nullable()->default('NULL');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('run_id')->references('id')->on('payroll_runs');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_run_histories');
    }
}