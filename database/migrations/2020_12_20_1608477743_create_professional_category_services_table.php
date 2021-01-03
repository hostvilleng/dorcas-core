<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessionalCategoryServicesTable extends Migration
{
    public function up()
    {
        Schema::create('professional_category_services', function (Blueprint $table) {

		$table->integer('professional_category_id',)->unsigned();
		$table->bigInteger('professional_service_id',)->unsigned();
		$table->primary(['professional_category_id','professional_service_id']);
		$table->foreign('professional_category_id')->references('id')->on('professional_categories');		$table->foreign('professional_service_id')->references('id')->on('professional_services');
        });
    }

    public function down()
    {
        Schema::dropIfExists('professional_category_services');
    }
}