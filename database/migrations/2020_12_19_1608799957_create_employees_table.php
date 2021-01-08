<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->integer('company_id')->unsigned();
		$table->integer('user_id')->unsigned()->nullable();
		$table->integer('department_id')->unsigned()->nullable();
		$table->integer('location_id')->unsigned()->nullable();
		$table->char('firstname',30);
		$table->char('lastname',30);
		$table->enum('gender',['female','male'])->nullable()->default('male');
		$table->decimal('salary_amount',10,2)->nullable();
		$table->enum('salary_period',['month','year'])->default('month');
		$table->char('staff_code',30)->nullable();
		$table->char('job_title',100)->nullable();
		$table->char('email',150)->nullable();
		$table->char('phone',30)->nullable();
		$table->date('hired_at')->nullable();
		$table->timestamp('deleted_at')->nullable();
		$table->timestamps();
		$table->foreign('company_id')->references('id')->on('companies');		$table->foreign('department_id')->references('id')->on('departments');		$table->foreign('location_id')->references('id')->on('locations');		$table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}