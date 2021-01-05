<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainIssuancesTable extends Migration
{
    public function up()
    {
        Schema::create('domain_issuances', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->char('uuid',50);
		$table->integer('domain_id')->unsigned()->nullable();
		$table->char('domainable_type',50)->nullable();
		$table->integer('domainable_id')->unsigned()->nullable();
		$table->char('prefix',80)->nullable();
		$table->timestamps();
		$table->foreign('domain_id')->references('id')->on('domains');
        });
    }

    public function down()
    {
        Schema::dropIfExists('domain_issuances');
    }
}