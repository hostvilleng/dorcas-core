<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorizerRequestMailsTable extends Migration
{
    public function up()
    {
        Schema::create('authorizer_request_mails', function (Blueprint $table) {

		$table->increments(id);
		$table->bigInteger('authorizer_id',)->unsigned();
		$table->bigInteger('request_id',)->unsigned();
		$table->tinyInteger('mail_action',)->default('0');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('authorizer_request_mails');
    }
}