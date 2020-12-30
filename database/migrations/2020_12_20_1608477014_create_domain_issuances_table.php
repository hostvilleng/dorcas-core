<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainIssuancesTable extends Migration
{
    public function up()
    {
        Schema::create('domain_issuances', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',50);
		$table->bigInteger('domain_id',)->unsigned()->nullable()->default('NULL');
		$table->char('domainable_type',50)->nullable()->default('NULL');
		$table->integer('domainable_id',)->unsigned()->nullable()->default('NULL');
		$table->char('prefix',80)->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary('id');
		$table->foreign('domain_id')->references('id')->on('domains');
        });
    }

    public function down()
    {
        Schema::dropIfExists('domain_issuances');
    }
}