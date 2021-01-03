<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationCategoryTable extends Migration
{
    public function up()
    {
        Schema::create('application_category', function (Blueprint $table) {

		$table->bigInteger('application_id',)->unsigned();
		$table->integer('application_category_id',)->unsigned();

        });
    }

    public function down()
    {
        Schema::dropIfExists('application_category');
    }
}