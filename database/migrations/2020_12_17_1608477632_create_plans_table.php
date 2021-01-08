<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->char('name',80);
		$table->decimal('price_monthly',10,2)->default('0.00');
		$table->decimal('price_yearly',10,2)->default('0.00');
		$table->timestamp('deleted_at')->nullable();
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('plans');
    }
}