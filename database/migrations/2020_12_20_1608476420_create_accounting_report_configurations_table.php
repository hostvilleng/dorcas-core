<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingReportConfigurationsTable extends Migration
{
    public function up()
    {
        Schema::create('accounting_report_configurations', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',50);
		$table->integer('company_id',)->unsigned();
		$table->char('report_name',80);
		$table->text('configuration');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary('id');
		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_report_configurations');
    }
}