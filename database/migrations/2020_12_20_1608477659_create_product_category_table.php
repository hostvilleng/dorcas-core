<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCategoryTable extends Migration
{
    public function up()
    {
        Schema::create('product_category', function (Blueprint $table) {

		$table->bigInteger('product_id',)->unsigned();
		$table->integer('product_category_id',)->unsigned();
		$table->primary(['product_id','product_category_id']);
		$table->foreign('product_category_id')->references('id')->on('product_categories');		$table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_category');
    }
}