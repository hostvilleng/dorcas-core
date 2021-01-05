<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('bill_payments', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->integer('company_id')->unsigned();
		$table->integer('plan_id')->unsigned();
		$table->char('reference',50);
		$table->char('processor',50);
		$table->char('currency',3)->default('NGN');
		$table->decimal('amount',12,2)->default(0.00);
		$table->text('json_data');
		$table->tinyInteger('is_successful')->default(0);
		$table->timestamps();
		$table->foreign('company_id')->references('id')->on('companies');		$table->foreign('plan_id')->references('id')->on('plans');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bill_payments');
    }
}