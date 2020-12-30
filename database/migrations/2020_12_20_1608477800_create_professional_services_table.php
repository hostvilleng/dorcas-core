<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessionalServicesTable extends Migration
{
    public function up()
    {
        Schema::create('professional_services', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',50);
		$table->integer('user_id',)->unsigned();
		$table->string('title',300);
		$table->enum('type',['professional','vendor'])->default('professional');
		$table->enum('cost_type',['free','paid'])->default('paid');
		$table->enum('cost_frequency',['hour','day','week','month','standard'])->nullable()->default('NULL');
		$table->char('cost_currency',3)->default('NGN');
		$table->decimal('cost_amount',10,2)->default('0.00');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary('id');
		$table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('professional_services');
    }
}