<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',50);
		$table->enum('type',['upgrade','regular'])->default('regular');
		$table->integer('plan_id',)->unsigned()->nullable()->default('NULL');
		$table->integer('user_id',)->unsigned()->nullable()->default('NULL');
		$table->char('code',30);
		$table->char('currency',3)->default('NGN');
		$table->decimal('amount',12,2)->default('0.00');
		$table->integer('max_usages',)->unsigned()->default('1');
		$table->text('description');
		$table->text('extra_data');
		$table->timestamp('expires_at')->nullable()->default('NULL');
		$table->timestamp('deleted_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary('id');
		$table->foreign('plan_id')->references('id')->on('plans');		$table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}