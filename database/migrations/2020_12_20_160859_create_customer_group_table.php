<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerGroupTable extends Migration
{
    public function up()
    {
        Schema::create('customer_group', function (Blueprint $table) {

		$table->integer('customer_id')->unsigned();
		$table->integer('group_id')->unsigned();
		$table->timestamp('created_at');
		$table->primary(['customer_id','group_id']);
        $table->foreign('customer_id')->references('id')->on('customers');
        $table->foreign('group_id')->references('id')->on('groups');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_group');
    }
}