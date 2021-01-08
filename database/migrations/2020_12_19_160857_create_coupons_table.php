<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->enum('type',['upgrade','regular'])->default('regular');
		$table->integer('plan_id')->unsigned()->nullable();
		$table->integer('user_id')->unsigned()->nullable();
		$table->char('code',30);
		$table->char('currency',3)->default('NGN');
		$table->decimal('amount',12,2)->default(0.00);
		$table->integer('max_usages',)->unsigned();
		$table->text('description');
		$table->text('extra_data');
		$table->timestamp('expires_at')->nullable();
		$table->timestamp('deleted_at')->nullable();
		$table->timestamps();
		$table->foreign('plan_id')->references('id')->on('plans');
		$table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}