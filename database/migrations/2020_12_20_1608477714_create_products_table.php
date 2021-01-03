<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',50);
		$table->integer('company_id',)->unsigned();
		$table->char('name',80);
		;
		$table->decimal('unit_price',10,2)->default('0.00');
		$table->integer('inventory',)->unsigned()->default('0');
		$table->string('product_type',25)->default('default');
		$table->string('product_variant')->default('');
		$table->timestamp('deleted_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->char('product_parent',50)->default('');
		$table->string('product_variant_type',25)->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}