<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxElementsTable extends Migration
{
    public function up()
    {
        Schema::create('tax_elements', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->integer('tax_authority_id',)->unsigned();
		$table->char('uuid',36);
		$table->string('element_name');
		$table->enum('element_type',['percentage','fixed']);
		$table->tinyInteger('isActive',1)->default('1');
		$table->enum('frequency',['yearly','monthly']);
		$table->json('type_data');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		;
		$table->datetime('frequency_year')->nullable()->default('NULL');
		$table->integer('frequency_month',)->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('tax_authority_id')->references('id')->on('tax_authorities');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_elements');
    }
}