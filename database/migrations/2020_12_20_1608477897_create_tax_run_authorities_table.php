<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxRunAuthoritiesTable extends Migration
{
    public function up()
    {
        Schema::create('tax_run_authorities', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->bigInteger('run_id',)->unsigned();
		$table->bigInteger('authority_id',)->unsigned();
		$table->string('amount');
		$table->string('status')->default('tax run completed');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_run_authorities');
    }
}