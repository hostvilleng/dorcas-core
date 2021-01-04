<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductImagesTable extends Migration
{
    public function up()
    {
        Schema::create('product_images', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->char('uuid',50);
		$table->integer('product_id')->unsigned();
		$table->string('url',300);
		$table->timestamps();
		$table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_images');
    }
}