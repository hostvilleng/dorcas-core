<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllowancePayrollPaygroupTable extends Migration
{
    public function up()
    {
        Schema::create('allowance_payroll_paygroup', function (Blueprint $table) {

		$table->increments('id');
		$table->integer('payroll_paygroup_id')->unsigned();
		$table->timestamps();
		$table->integer('payroll_allowances_Id')->unsigned();
		$table->foreign('payroll_paygroup_id')->references('id')->on('payroll_paygroup');
        });
    }

    public function down()
    {
        Schema::dropIfExists('allowance_payroll_paygroup');
    }
}