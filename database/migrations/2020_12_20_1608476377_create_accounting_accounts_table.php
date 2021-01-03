<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('accounting_accounts', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',50);
		$table->integer('company_id',)->unsigned();
		$table->bigInteger('parent_account_id',)->unsigned()->nullable()->default('NULL');
		$table->char('name',70);
		$table->string('display_name',300)->nullable()->default('NULL');
		$table->enum('entry_type',['credit','debit']);
		$table->tinyInteger('is_visible',)->default('1');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->string('account_code',45)->nullable()->default('NULL');
		$table->string('account_type',45)->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('company_id')->references('id')->on('companies');		$table->foreign('parent_account_id')->references('id')->on('accounting_accounts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_accounts');
    }
}