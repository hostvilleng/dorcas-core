<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthClientsTable extends Migration
{
    public function up()
    {
        Schema::create('oauth_clients', function (Blueprint $table) {

		$table->increments('id')->unsigned();
		$table->integer('user_id')->nullable()->default('NULL');
		$table->string('name');
		$table->string('secret',100);
		$table->text('redirect');
		$table->tinyInteger('personal_access_client',1);
		$table->tinyInteger('password_client',1);
		$table->tinyInteger('revoked',1);
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('oauth_clients');
    }
}