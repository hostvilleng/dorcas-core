<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessionalCredentialsTable extends Migration
{
    public function up()
    {
        Schema::create('professional_credentials', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->char('uuid',50);
		$table->integer('user_id')->unsigned();
		$table->char('title',255);
		$table->char('type',50);
		$table->text('description');
		;
		$table->string('certification',100)->nullable();
		$table->timestamps();
		$table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('professional_credentials');
    }
}