<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxRunHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_run_authorities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('run_id');
            $table->unsignedBigInteger('authority_id');
            $table->string('amount');
            $table->string('status')->default('tax run completed');
            $table->foreign('run_id')->references('id')->on('tax_runs')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('authority_id')->references('id')->on('tax_authorities')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('payroll_run_employees');
    }
}
