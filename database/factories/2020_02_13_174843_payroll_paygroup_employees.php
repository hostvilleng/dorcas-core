<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PayrollPaygroupEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_paygroup_employees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('paygroup_id',false);
            $table->unsignedInteger('employee_id',false);
            $table->timestamps();
            $table->foreign('paygroup_id')->references('id')->on('payroll_paygroup')->onDelete('cascade')->onUpdate('cascade');
//            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('payroll_paygroup_employees');

        Schema::enableForeignKeyConstraints();
    }
}
