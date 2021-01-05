<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessionalCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('professional_categories', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->char('uuid',50);
		$table->integer('parent_id')->unsigned()->nullable();
		$table->char('name',80);
		$table->timestamps();
		// $table->foreign('parent_id')->references('id')->on('professional_categories');
        });
    }

    public function down()
    {
        Schema::dropIfExists('professional_categories');
    }
}