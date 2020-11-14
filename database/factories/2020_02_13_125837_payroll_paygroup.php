<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PayrollPaygroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_paygroup', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id',false);
            $table->uuid('uuid')->unique();
            $table->string('group_name');
            $table->boolean('isActive')->default(1);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_paygroup');
    }
}
