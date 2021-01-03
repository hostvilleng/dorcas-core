<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollAllowancesTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_allowances', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->integer('company_id',)->unsigned();
		$table->integer('payroll_authority_id',)->unsigned()->nullable()->default('NULL');
		$table->char('uuid',36);
		$table->string('allowance_name');
		$table->enum('model',['percent_of_base','fixed','computational']);
		$table->tinyInteger('isActive',1)->default('1');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->enum('allowance_type',['benefit','deduction']);
		$table->json('model_data')->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('company_id')->references('id')->on('companies');		$table->foreign('payroll_authority_id')->references('id')->on('payroll_authorities');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_allowances');
    }
}