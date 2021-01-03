<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollAuthoritiesTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_authorities', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->integer('company_id',)->unsigned();
		$table->char('uuid',36);
		$table->string('authority_name');
		$table->enum('payment_mode',['paystack','flutterwave']);
		$table->json('default_payment_details');
		$table->json('payment_details');
		$table->tinyInteger('isActive',1)->default('1');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_authorities');
    }
}