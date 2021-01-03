<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalsTable extends Migration
{
    public function up()
    {
        Schema::create('approvals', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->char('uuid',36);
		$table->string('title');
		$table->enum('scope_type',['key_person','min_number','both']);
		$table->json('scope_data')->nullable()->default('NULL');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->integer('company_id',)->unsigned();
		$table->timestamp('deleted_at')->nullable()->default('NULL');
		$table->tinyInteger('active',)->default('1');
		$table->enum('frequency_type',['sequential','random']);
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('approvals');
    }
}