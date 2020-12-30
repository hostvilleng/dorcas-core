<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMliResourcesTable extends Migration
{
    public function up()
    {
        Schema::create('mli_resources', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->integer('partner_id',)->nullable()->default('NULL');
		$table->string('resource_uuid',40)->nullable()->default('NULL');
		$table->tinyInteger('resource_category',)->nullable()->default('NULL');
		$table->tinyInteger('resource_subcategory',)->nullable()->default('NULL');
		$table->string('resource_type',50)->nullable()->default('NULL');
		$table->string('resource_subtype',50)->nullable()->default('NULL');
		$table->string('resource_thumb')->nullable()->default('NULL');
		$table->string('resource_image')->nullable()->default('NULL');
		$table->string('resource_video')->nullable()->default('NULL');
		$table->string('resource_title',100)->nullable()->default('NULL');
		$table->string('resource_description')->nullable()->default('NULL');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('mli_resources');
    }
}