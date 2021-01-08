<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductStocksTable extends Migration
{
    public function up()
    {
        Schema::create('product_stocks', function (Blueprint $table) {

		$table->increments('id');
		$table->integer('product_id')->unsigned();
		$table->char('action',20)->default('add');
		$table->integer('quantity')->unsigned();
		$table->string('comment',300)->nullable();
		$table->timestamps();
		$table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stocks');
    }
}