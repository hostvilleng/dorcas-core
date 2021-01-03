<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',50);
		$table->bigInteger('order_id',)->unsigned();
		$table->bigInteger('customer_id',)->unsigned();
		$table->char('channel',50)->default('paystack');
		$table->decimal('amount',10,2)->default('0.00');
		$table->char('currency',3)->default('NGN');
		$table->char('reference',50);
		$table->char('response_code',10)->nullable()->default('NULL');
		$table->char('response_description',150)->nullable()->default('NULL');
		$table->text('json_payload');
		$table->tinyInteger('is_successful',)->default('0');
		$table->timestamp('deleted_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary('id');
		$table->foreign('customer_id')->references('id')->on('customers');		$table->foreign('order_id')->references('id')->on('orders');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
}