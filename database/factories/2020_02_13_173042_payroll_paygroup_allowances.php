<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PayrollPaygroupAllowances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_paygroup_allowances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('paygroup_id',false);
            $table->unsignedInteger('allowance_id',false);
            $table->timestamps();
            $table->foreign('paygroup_id')->references('id')->on('payroll_paygroup')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('allowance_id')->references('id')->on('payroll_allowances')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('payroll_paygroup_allowances');
        Schema::enableForeignKeyConstraints();

    }
}
