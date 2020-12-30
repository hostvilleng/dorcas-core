<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalAuthorizersTable extends Migration
{
    public function up()
    {
        Schema::create('approval_authorizers', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->bigInteger('user_id',)->unsigned();
		$table->bigInteger('approval_id',)->unsigned();
		$table->enum('approval_scope',['critical','standard'])->nullable()->default('NULL');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_authorizers');
    }
}