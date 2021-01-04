<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {

			$table->integer('id')->primary()->unsigned();
			$table->char('uuid',50);
			$table->integer('company_id',)->unsigned();
			$table->char('name',80);
			$table->decimal('unit_price',10,2)->default(0.00);
			$table->integer('inventory')->unsigned()->default(0);
			$table->string('product_type',25);
			$table->string('product_variant');
			$table->timestamp('deleted_at')->nullable();
			$table->timestamps();
			$table->char('product_parent',50)->default('');
			$table->string('product_variant_type',25)->nullable();
			$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}