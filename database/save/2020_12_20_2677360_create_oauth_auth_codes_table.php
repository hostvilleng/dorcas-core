<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthAuthCodesTable extends Migration
{
    public function up()
    {
        Schema::create('oauth_auth_codes', function (Blueprint $table) {

		$table->string('id',100);
		$table->integer('user_id');
		$table->integer('client_id');
		$table->text('scopes');
		$table->tinyInteger('revoked',1);
		$table->datetime('expires_at')->nullable()->default('NULL');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('oauth_auth_codes');
    }
}