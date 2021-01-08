<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->char('name',50);
		$table->char('display_name',255);
		$table->text('description');
		$table->string('icon',400)->nullable();
		$table->tinyInteger('is_paid')->default(0);
		$table->timestamp('deleted_at')->nullable();
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
}