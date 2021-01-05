<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeePayrollPaygroupTable extends Migration
{
    public function up()
    {
        Schema::create('employee_payroll_paygroup', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->integer('payroll_paygroup_id',)->unsigned();
		$table->integer('employee_id')->unsigned();
		$table->timestamps();
		$table->foreign('payroll_paygroup_id')->references('id')->on('payroll_paygroup');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_payroll_paygroup');
    }
}