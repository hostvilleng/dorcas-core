<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationInstallsTable extends Migration
{
    public function up()
    {
        Schema::create('application_installs', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',36);
		$table->integer('application_id')->unsigned();
		$table->integer('company_id')->unsigned();
		$table->text('extra_json');
		$table->timestamps();
        $table->foreign('application_id')->references('id')->on('applications');		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('application_installs');
    }
}