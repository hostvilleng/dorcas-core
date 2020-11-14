<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PayrollRunHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_run_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('employee_id',false);
            $table->unsignedInteger('run_id',false);
            $table->boolean('status');
            $table->json('status_data');
            $table->foreign('run_id')->references('id')->on('payroll_runs')->onDelete('cascade')->onUpdate('cascade');
//            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_run_histories');
    }
}
