<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealsTable extends Migration
{
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->char('uuid',36);
		$table->integer('customer_id')->unsigned();
		$table->char('name',80);
		$table->char('value_currency',3)->default('NGN');
		$table->decimal('value_amount',12,2)->default(0.00);
		$table->text('note');
		$table->timestamps();
		$table->foreign('customer_id')->references('id')->on('customers');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deals');
    }
}