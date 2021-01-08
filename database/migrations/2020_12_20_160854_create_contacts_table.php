<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->integer('company_id')->unsigned();
		$table->char('contactable_type',50);
		$table->bigInteger('contactable_id');
		$table->char('type',50)->default('vendor');
		$table->char('firstname',30)->nullable();
		$table->char('lastname',30)->nullable();
		$table->char('email',100)->nullable();
		$table->char('phone',30)->nullable();
		$table->timestamps();
		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}