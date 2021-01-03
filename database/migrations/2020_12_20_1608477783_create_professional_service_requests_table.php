<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessionalServiceRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('professional_service_requests', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',50);
		$table->integer('company_id',)->unsigned();
		$table->bigInteger('service_id',)->unsigned();
		$table->text('message');
		$table->string('attachment_url',600)->nullable()->default('NULL');
		$table->tinyInteger('is_read',)->default('0');
		$table->enum('status',['accepted','rejected','pending'])->default('pending');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary('id');
		$table->foreign('company_id')->references('id')->on('companies');		$table->foreign('service_id')->references('id')->on('professional_services');
        });
    }

    public function down()
    {
        Schema::dropIfExists('professional_service_requests');
    }
}