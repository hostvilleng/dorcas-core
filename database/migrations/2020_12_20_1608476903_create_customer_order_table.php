<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerOrderTable extends Migration
{
    public function up()
    {
        Schema::create('customer_order', function (Blueprint $table) {

		$table->bigInteger('customer_id',)->unsigned();
		$table->bigInteger('order_id',)->unsigned();
		$table->tinyInteger('is_paid',)->default('0');
		$table->timestamp('paid_at')->nullable()->default('NULL');
		$table->primary(['customer_id','order_id']);
		$table->foreign('customer_id')->references('id')->on('customers');		$table->foreign('order_id')->references('id')->on('orders');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_order');
    }
}