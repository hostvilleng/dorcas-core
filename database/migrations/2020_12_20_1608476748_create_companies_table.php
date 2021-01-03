<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {

		$table->increments(id)->unsigned();
		$table->char('uuid',50);
		$table->integer('plan_id',)->unsigned()->default('1');
		$table->char('reg_number',30)->nullable()->default('NULL');
		$table->char('name',100);
		$table->char('phone',30)->nullable()->default('NULL');
		$table->char('email',200)->nullable()->default('NULL');
		$table->string('website',100)->nullable()->default('NULL');
		$table->enum('plan_type',['monthly','yearly'])->default('monthly');
		$table->text('extra_data');
		$table->string('logo_url',400)->nullable()->default('NULL');
		$table->timestamp('access_expires_at')->nullable()->default('NULL');
		$table->timestamp('deleted_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->string('prefix',10)->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('plan_id')->references('id')->on('plans');
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
}