<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorizerRequestGrantsTable extends Migration
{
    public function up()
    {
        Schema::create('authorizer_request_grants', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->integer('authorizer_id')->unsigned();
		$table->integer('request_id')->unsigned();
		$table->tinyInteger('status')->default(0);
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('authorizer_request_grants');
    }
}