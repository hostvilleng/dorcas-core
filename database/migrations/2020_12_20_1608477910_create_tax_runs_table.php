<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxRunsTable extends Migration
{
    public function up()
    {
        Schema::create('tax_runs', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->integer('tax_element_id',)->unsigned();
		$table->char('uuid',36);
		$table->string('run_name');
		$table->tinyInteger('isActive',1)->default('1');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->enum('status',['running','processed'])->default('running');
		$table->primary('id');
		$table->foreign('tax_element_id')->references('id')->on('tax_elements');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_runs');
    }
}