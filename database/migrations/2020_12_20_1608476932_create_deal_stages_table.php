<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealStagesTable extends Migration
{
    public function up()
    {
        Schema::create('deal_stages', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',36);
		$table->bigInteger('deal_id',)->unsigned();
		$table->char('name',80);
		$table->decimal('value_amount',12,2)->default('0.00');
		$table->text('note');
		$table->timestamp('entered_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('deal_id')->references('id')->on('deals');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deal_stages');
    }
}