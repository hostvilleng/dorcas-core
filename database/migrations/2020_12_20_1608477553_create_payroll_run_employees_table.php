<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollRunEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_run_employees', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->integer('employee_id',)->unsigned();
		$table->integer('run_id',)->unsigned()->nullable()->default('NULL');
		$table->string('amount');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->string('paygroup_ids',45)->nullable()->default('NULL');
		$table->json('invoice_data')->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('run_id')->references('id')->on('payroll_runs');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_run_employees');
    }
}