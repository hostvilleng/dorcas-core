<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxAuthortities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('tax_authorities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id',false);
            $table->uuid('uuid')->unique();
            $table->string('authority_name');
            $table->enum('payment_mode',["Paystack","Flutterwave"]);
            $table->boolean('isActive')->default(1);
            $table->json('default_payment_details');
            $table->json('payment_details');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_authorities');
    }
}
