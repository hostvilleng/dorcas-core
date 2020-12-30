<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingEntriesTable extends Migration
{
    public function up()
    {
        Schema::create('accounting_entries', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',50);
		$table->bigInteger('account_id',)->unsigned()->nullable()->default('NULL');
		$table->enum('entry_type',['credit','debit']);
		$table->char('currency',3)->default('NGN');
		$table->decimal('amount',12,2)->default('0.00');
		$table->string('memo',300)->nullable()->default('NULL');
		$table->char('source_type',50)->default('manual');
		$table->string('source_info',300)->default('manual');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary('id');
		$table->foreign('account_id')->references('id')->on('accounting_accounts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_entries');
    }
}