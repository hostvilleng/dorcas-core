<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_group', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('group_id');
            $table->string('group_type');
            $table->string('duration_days');
            $table->enum('duration_term',['annual']);
            $table->foreign('type_id')->references('id')->on('leave_types')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('teams')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::dropIfExists('leave_group');
    }
}
