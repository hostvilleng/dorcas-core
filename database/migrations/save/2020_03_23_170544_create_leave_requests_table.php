<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('approval_id');
            $table->bigInteger('count_available');
            $table->bigInteger('count_utilized');
            $table->bigInteger('count_remaining');
            $table->bigInteger('count_requesting');
            $table->date('data_start_date');
            $table->string('data_report_back');
            $table->string('data_contact_address');
            $table->string('data_contact_phone');
            $table->string('data_backup_staff');
            $table->string('data_remarks');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('group_id')->references('id')->on('leave_group')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('approval_id')->references('id')->on('approvals')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('leave_requests');
    }
}
