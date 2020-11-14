<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalsAuthorizersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approvals_authorizers', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('approval_id');
            $table->foreign('approval_id')->references('id')->on('approvals')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('approval_scope',['critical','standard']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approvals_authorizers');
    }
}
