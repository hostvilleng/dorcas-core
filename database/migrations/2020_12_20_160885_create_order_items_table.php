<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->integer('order_id')->unsigned();
		$table->integer('product_id')->unsigned();
		$table->integer('quantity')->unsigned()->default(1);
		$table->decimal('unit_price',10,2)->default(0.00);
        $table->foreign('order_id')->references('id')->on('orders');
        $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}