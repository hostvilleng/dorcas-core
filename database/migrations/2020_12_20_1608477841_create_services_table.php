<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->char('uuid',50);
		$table->char('name',50);
		$table->char('display_name',255);
		$table->text('description');
		$table->string('icon',400)->nullable()->default('NULL');
		$table->tinyInteger('is_paid',1)->default('0');
		$table->timestamp('deleted_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
}