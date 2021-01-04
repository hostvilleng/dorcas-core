<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxRunAuthoritiesTable extends Migration
{
    public function up()
    {
        Schema::create('tax_run_authorities', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->bigInteger('run_id')->unsigned();
		$table->bigInteger('authority_id')->unsigned();
		$table->string('amount');
		$table->string('status')->default('tax run completed');
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_run_authorities');
    }
}