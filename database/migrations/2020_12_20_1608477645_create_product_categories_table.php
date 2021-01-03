<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->char('uuid',50);
		$table->integer('company_id',)->unsigned();
		$table->char('name',80);
		$table->char('slug',80);
		$table->text('description');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary('id');
		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
}