<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollRunAuthoritiesTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_run_authorities', function (Blueprint $table) {

			$table->increments('id');
			$table->integer('authority_id')->unsigned();
			$table->integer('run_id')->unsigned()->nullable();
			$table->string('amount');
			$table->timestamps();
			$table->integer('allowance_id')->unsigned();
			$table->integer('employee_id')->unsigned();
			$table->foreign('authority_id')->references('id')->on('payroll_authorities');	
			$table->foreign('run_id')->references('id')->on('payroll_runs');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_run_authorities');
    }
}