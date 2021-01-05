<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->char('uuid',50);
		$table->integer('order_id',)->unsigned();
		$table->integer('customer_id')->unsigned();
		$table->char('channel',50)->default('paystack');
		$table->decimal('amount',10,2)->default(0.00);
		$table->char('currency',3)->default('NGN');
		$table->char('reference',50);
		$table->char('response_code',10)->nullable();
		$table->char('response_description',150)->nullable();
		$table->text('json_payload');
		$table->tinyInteger('is_successful')->default(0);
		$table->timestamp('deleted_at')->nullable();
		$table->timestamps();
		$table->foreign('customer_id')->references('id')->on('customers');
		$table->foreign('order_id')->references('id')->on('orders');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
}