<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollPaygroupTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_paygroup', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->integer('company_id')->unsigned();
		$table->char('uuid',36);
		$table->string('group_name');
		$table->tinyInteger('isActive');
		$table->timestamps();
		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_paygroup');
    }
}