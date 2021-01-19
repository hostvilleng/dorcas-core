<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollAllowancesTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_allowances', function (Blueprint $table) {

		$table->increments('id');
		$table->integer('company_id')->unsigned();
		$table->integer('payroll_authority_id')->unsigned();
		$table->char('uuid',36);
		$table->string('allowance_name');
		$table->enum('model',['percent_of_base','fixed','computational']);
		$table->tinyInteger('isActive');
		$table->timestamps();
		$table->enum('allowance_type',['benefit','deduction']);
		$table->json('model_data')->nullable();
		$table->foreign('company_id')->references('id')->on('companies');		
		$table->foreign('payroll_authority_id')->references('id')->on('payroll_authorities');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_allowances');
    }
}