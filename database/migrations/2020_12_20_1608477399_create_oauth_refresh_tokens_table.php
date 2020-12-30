<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthRefreshTokensTable extends Migration
{
    public function up()
    {
        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {

		$table->string('id',100);
		$table->string('access_token_id',100);
		$table->tinyInteger('revoked',1);
		$table->datetime('expires_at')->nullable()->default('NULL');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('oauth_refresh_tokens');
    }
}