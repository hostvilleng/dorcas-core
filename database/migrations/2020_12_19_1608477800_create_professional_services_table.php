<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessionalServicesTable extends Migration
{
    public function up()
    {
        Schema::create('professional_services', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->integer('user_id')->unsigned();
		$table->string('title',300);
		$table->enum('type',['professional','vendor'])->default('professional');
		$table->enum('cost_type',['free','paid'])->default('paid');
		$table->enum('cost_frequency',['hour','day','week','month','standard'])->nullable();
		$table->char('cost_currency',3)->default('NGN');
		$table->decimal('cost_amount',10,2)->default(0.00);
		$table->timestamps();
		$table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('professional_services');
    }
}