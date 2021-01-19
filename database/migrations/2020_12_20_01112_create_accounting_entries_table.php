<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingEntriesTable extends Migration
{
    public function up()
    {
        Schema::create('accounting_entries', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->integer('account_id')->unsigned()->nullable();
		$table->enum('entry_type',['credit','debit']);
		$table->char('currency',3)->default('NGN');
		$table->decimal('amount',12,2)->default(0.00);
		$table->string('memo',300)->nullable();
		$table->char('source_type',50)->default('manual');
		$table->string('source_info',300)->default('manual');
		$table->timestamps();
		$table->foreign('account_id')->references('id')->on('accounting_accounts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_entries');
    }
}