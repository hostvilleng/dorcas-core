<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranstrakMailsTable extends Migration
{
    public function up()
    {
        Schema::create('transtrak_mails', function (Blueprint $table) {

		$table->bigInteger('company_id',)->unsigned();
		$table->string('transtrak_mail',45);

        });
    }

    public function down()
    {
        Schema::dropIfExists('transtrak_mails');
    }
}