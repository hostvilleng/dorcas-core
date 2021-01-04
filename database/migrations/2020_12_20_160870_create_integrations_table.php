<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationsTable extends Migration
{
    public function up()
    {
        Schema::create('integrations', function (Blueprint $table) {

		$table->integer('id')->primary()->unsigned();
		$table->char('uuid',50);
		$table->integer('company_id')->unsigned();
		$table->char('type',30);
		$table->char('name',50);
		$table->text('configuration');
		$table->timestamps();
		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('integrations');
    }
}