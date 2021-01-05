<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertsTable extends Migration
{
    public function up()
    {
        Schema::create('adverts', function (Blueprint $table) {

		$table->integer('id')->unsigned();
		$table->char('uuid',36);
		$table->integer('company_id')->unsigned();
		$table->integer('poster_id')->unsigned();
		$table->char('type',50)->default('sidebar');
		$table->char('title',80)->nullable();
		$table->char('image_filename',255)->nullable();
		$table->string('redirect_url',500)->nullable();
		$table->text('extra_data');
		$table->tinyInteger('is_default',1);
		$table->timestamps();
		$table->foreign('company_id')->references('id')->on('companies');		$table->foreign('poster_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('adverts');
    }
}