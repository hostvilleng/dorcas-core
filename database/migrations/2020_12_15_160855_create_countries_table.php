<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->char('uuid',50);
		$table->char('name',80);
		$table->char('iso_code',5);
		$table->char('dialing_code',10)->nullable();
		$table->timestamp('deleted_at')->nullable();
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('countries');
    }
}