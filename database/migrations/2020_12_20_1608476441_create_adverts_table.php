<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertsTable extends Migration
{
    public function up()
    {
        Schema::create('adverts', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',36);
		$table->integer('company_id',)->unsigned();
		$table->integer('poster_id',)->unsigned();
		$table->char('type',50)->default('sidebar');
		$table->char('title',80)->nullable()->default('NULL');
		$table->char('image_filename',255)->nullable()->default('NULL');
		$table->string('redirect_url',500)->nullable()->default('NULL');
		$table->text('extra_data');
		$table->tinyInteger('is_default',1)->default('0');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('company_id')->references('id')->on('companies');		$table->foreign('poster_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('adverts');
    }
}