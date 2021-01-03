<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollRunsTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_runs', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->char('uuid',36);
		$table->string('title');
		$table->enum('status',['draft','approved','processed'])->default('draft');
		$table->string('run');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->integer('company_id',)->unsigned();
		$table->primary(['id','run']);

        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_runs');
    }
}