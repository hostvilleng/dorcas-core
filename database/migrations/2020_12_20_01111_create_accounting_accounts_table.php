<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('accounting_accounts', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->integer('company_id')->unsigned();
		$table->integer('parent_account_id')->unsigned()->nullable();
		$table->char('name',70);
		$table->string('display_name',300)->nullable();
		$table->enum('entry_type',['credit','debit']);
		$table->tinyInteger('is_visible')->default('1');
		$table->timestamps();
		$table->string('account_code',45)->nullable();
		$table->string('account_type',45)->nullable();
		$table->foreign('company_id')->references('id')->on('companies');		
		// $table->foreign('parent_account_id')->references('id')->on('accounting_accounts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_accounts');
    }
}