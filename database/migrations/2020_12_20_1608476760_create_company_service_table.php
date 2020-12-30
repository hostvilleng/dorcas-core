<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyServiceTable extends Migration
{
    public function up()
    {
        Schema::create('company_service', function (Blueprint $table) {

		$table->integer('company_id',)->unsigned();
		$table->integer('service_id',)->unsigned();
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary(['company_id','service_id']);

        });
    }

    public function down()
    {
        Schema::dropIfExists('company_service');
    }
}