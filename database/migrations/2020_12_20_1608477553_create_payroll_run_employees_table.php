<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollRunEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_run_employees', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->integer('employee_id')->unsigned();
		$table->integer('run_id')->unsigned()->nullable();
		$table->string('amount');
		$table->timestamps();
		$table->string('paygroup_ids',45)->nullable();
		$table->json('invoice_data')->nullable();
		$table->foreign('run_id')->references('id')->on('payroll_runs');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_run_employees');
    }
}