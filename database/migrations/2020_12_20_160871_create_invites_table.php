<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitesTable extends Migration
{
    public function up()
    {
        Schema::create('invites', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->char('uuid',36);
		$table->integer('inviter_id')->unsigned();
		$table->char('inviter_type',50);
		$table->char('firstname',30);
		$table->char('lastname',30)->nullable();
		$table->string('email');
		$table->string('message')->nullable();
		$table->text('config_data');
		$table->enum('status',['pending','accepted','rejected'])->default('pending');
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('invites');
    }
}