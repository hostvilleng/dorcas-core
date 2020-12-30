<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnverifiedTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('unverified_transactions', function (Blueprint $table) {

		$table->increments(id);
		$table->char('uuid',80);
		$table->bigInteger('account_id',)->unsigned();
		$table->string('amount',45);
		$table->enum('entry_type',['credit','debit']);
		$table->enum('status',['verified','unverified'])->default('unverified');
		$table->string('remark',45)->nullable()->default('NULL');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->bigInteger('company_id',)->unsigned()->nullable()->default('NULL');
		$table->string('currency',45)->nullable()->default('NULL');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('unverified_transactions');
    }
}