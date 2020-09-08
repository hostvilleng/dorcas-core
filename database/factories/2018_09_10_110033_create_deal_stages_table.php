<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deal_stages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('deal_id', false);
            $table->char('name', 80);
            $table->unsignedDecimal('value_amount', 12, 2)->default(0.00);
            $table->text('note')->nullable();
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();
    
            $table->index(['deal_id', 'name', 'entered_at', 'value_amount'], 'ix_deal_stages');
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deal_stages');
    }
}
