<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitesTable extends Migration
{
    public function up()
    {
        Schema::create('invites', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',36);
		$table->integer('inviter_id',)->unsigned();
		$table->char('inviter_type',50);
		$table->char('firstname',30);
		$table->char('lastname',30)->nullable()->default('NULL');
		$table->string('email');
		$table->string('message')->nullable()->default('NULL');
		$table->text('config_data');
		$table->enum('status',['pending','accepted','rejected'])->default('pending');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('invites');
    }
}