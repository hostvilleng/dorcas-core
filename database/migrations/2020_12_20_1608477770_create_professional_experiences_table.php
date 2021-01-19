<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessionalExperiencesTable extends Migration
{
    public function up()
    {
        Schema::create('professional_experiences', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->integer('user_id')->unsigned();
		$table->char('company',80);
		$table->year('from_year');
		$table->year('to_year');
		$table->char('designation',60)->nullable();
		$table->timestamps();
		$table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('professional_experiences');
    }
}