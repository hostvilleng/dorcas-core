<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMliResourcesTable extends Migration
{
    public function up()
    {
        Schema::create('mli_resources', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->integer('partner_id')->nullable();
		$table->string('resource_uuid',40)->nullable();
		$table->tinyInteger('resource_category')->nullable();
		$table->tinyInteger('resource_subcategory')->nullable();
		$table->string('resource_type',50)->nullable();
		$table->string('resource_subtype',50)->nullable();
		$table->string('resource_thumb')->nullable();
		$table->string('resource_image')->nullable();
		$table->string('resource_video')->nullable();
		$table->string('resource_title',100)->nullable();
		$table->string('resource_description')->nullable();

        });
    }

    public function down()
    {
        Schema::dropIfExists('mli_resources');
    }
}