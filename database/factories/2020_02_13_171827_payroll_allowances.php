<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PayrollAllowances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_allowances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id',false);
            $table->unsignedInteger('payroll_authority_id',false)->nullable();
            $table->uuid('uuid')->unique();
            $table->string('allowance_name');
            $table->enum('model',['percent_of_base','fixed']);
            $table->tinyInteger('base_ratio');
            $table->tinyInteger('base_employer_ratio')->default(0);
            $table->boolean('isActive')->default(1);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('payroll_authority_id')->references('id')->on('payroll_authorities')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_allowances');
    }
}
