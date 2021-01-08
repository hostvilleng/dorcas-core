<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerContactsTable extends Migration
{
    public function up()
    {
        Schema::create('customer_contacts', function (Blueprint $table) {

		$table->integer('contact_field_id')->unsigned();
		$table->integer('customer_id')->unsigned();
		$table->char('value',100);
		$table->primary(['contact_field_id','customer_id']);
        $table->foreign('contact_field_id')->references('id')->on('contact_fields');
        $table->foreign('customer_id')->references('id')->on('customers');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_contacts');
    }
}