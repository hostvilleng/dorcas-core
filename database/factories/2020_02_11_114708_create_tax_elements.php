<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxElements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    
    public function up()
    {
        Schema::create('tax_elements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tax_authority_id',false);
            $table->uuid('uuid')->unique();
            $table->string('element_name');
            $table->enum('type',["computational","translational","percentage"]);
            $table->boolean('isActive')->default(1);
            $table->enum('duration',['monthly','yearly']);
            $table->json('type_data');
            $table->timestamps();
            $table->foreign('tax_authority_id')->references('id')->on('tax_authorities')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_elements');
    }
}
