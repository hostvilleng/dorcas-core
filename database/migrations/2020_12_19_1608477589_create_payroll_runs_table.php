<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollRunsTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_runs', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',36);
		$table->string('title');
		$table->enum('status',['draft','approved','processed'])->default('draft');
		$table->string('run');
		$table->timestamps();
		$table->integer('company_id')->unsigned();

        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_runs');
    }
}