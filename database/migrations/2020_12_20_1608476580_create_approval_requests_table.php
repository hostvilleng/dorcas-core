<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('approval_requests', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->char('uuid',36);
		$table->integer('approval_id',)->unsigned();
		$table->string('approval_comments')->nullable()->default('NULL');
		$table->enum('approval_status',['approved','declined','active'])->default('active');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('deleted_at')->nullable()->default('NULL');
		$table->integer('company_id',)->unsigned();
		$table->string('model',45)->nullable()->default('NULL');
		$table->integer('model_request_id',)->unsigned();
		$table->json('model_data')->nullable()->default('NULL');
		$table->string('rejection_comments')->nullable()->default('NULL');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_requests');
    }
}