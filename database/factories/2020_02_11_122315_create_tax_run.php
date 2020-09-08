<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxRun extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_runs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tax_element_id',false);
            $table->uuid('uuid')->unique();
            $table->string('run_name');
            $table->boolean('isActive')->default(1);
            $table->date('start_time');
            $table->date('end_time');
            $table->timestamps();
            $table->foreign('tax_element_id')->references('id')->on('tax_elements')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_runs');
    }
}
