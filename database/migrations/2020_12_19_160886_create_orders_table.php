<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->integer('company_id')->unsigned();
		$table->char('title',80);
		;
		$table->char('product_name',80)->nullable();
		$table->text('product_description');
		$table->integer('quantity')->default(0);
		$table->decimal('unit_price',10,2)->default(0.00);
		$table->char('currency',3)->default('NGN');
		$table->decimal('amount',10,2)->default(0.00);
		$table->date('due_at')->nullable();
		$table->tinyInteger('reminder_on')->default(0);
		$table->tinyInteger('is_quote')->default(0);
		$table->timestamp('deleted_at')->nullable();
		$table->timestamps();
		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}