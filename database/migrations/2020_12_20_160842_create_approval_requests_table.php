<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('approval_requests', function (Blueprint $table) {

			$table->integer('id')->primary()->unsigned();
			$table->char('uuid',36);
			$table->integer('approval_id')->unsigned();
			$table->string('approval_comments')->nullable();
			$table->enum('approval_status',['approved','declined','active'])->default('active');
			$table->integer('company_id')->unsigned();
			$table->string('model',45)->nullable();
			$table->integer('model_request_id')->unsigned();
			$table->json('model_data')->nullable();
			$table->string('rejection_comments')->nullable();
			$table->timestamps();
			$table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_requests');
    }
}