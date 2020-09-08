<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('customer_id', false);
            $table->char('name', 80);
            $table->char('value_currency', 3)->default('NGN');
            $table->unsignedDecimal('value_amount', 12, 2)->default(0.00);
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'name', 'value_currency', 'value_amount'], 'ix_deals');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deals');
    }
}
