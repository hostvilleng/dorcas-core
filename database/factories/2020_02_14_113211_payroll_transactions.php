<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PayrollTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('company_id',false);
            $table->unsignedInteger('employee_id',false);
            $table->unsignedInteger('run_id',false)->nullable();
            $table->string('amount');
            $table->boolean('status');
            $table->enum('status_type',['one_time','repeat']);
            $table->enum('amount_type',['deduction','addition']);
            $table->string('remarks');
            $table->date('end_time')->nullable();
            $table->foreign('run_id')->references('id')->on('payroll_runs')->onDelete('cascade')->onUpdate('cascade');
//            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('payroll_transactions');
    }
}
