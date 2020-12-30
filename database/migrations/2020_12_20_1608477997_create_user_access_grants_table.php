<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAccessGrantsTable extends Migration
{
    public function up()
    {
        Schema::create('user_access_grants', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',36);
		$table->integer('user_id',)->unsigned();
		$table->integer('company_id',)->unsigned();
		$table->text('access_token');
		$table->enum('status',['pending','accepted','rejected'])->default('pending');
		$table->text('extra_json');
		$table->timestamp('status_updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('user_access_grants');
    }
}