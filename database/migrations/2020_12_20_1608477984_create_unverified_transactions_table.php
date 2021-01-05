<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnverifiedTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('unverified_transactions', function (Blueprint $table) {

		$table->integer('id')->primary();
		$table->char('uuid',80);
		$table->bigInteger('account_id')->unsigned();
		$table->string('amount',45);
		$table->enum('entry_type',['credit','debit']);
		$table->enum('status',['verified','unverified'])->default('unverified');
		$table->string('remark',45)->nullable();
		$table->timestamps();
		$table->integer('company_id',)->unsigned()->nullable();
		$table->string('currency',45)->nullable();

        });
    }

    public function down()
    {
        Schema::dropIfExists('unverified_transactions');
    }
}