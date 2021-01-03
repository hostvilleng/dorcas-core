<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainsTable extends Migration
{
    public function up()
    {
        Schema::create('domains', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',50);
		$table->char('domainable_type',50);
		$table->integer('domainable_id',);
		$table->char('domain',80);
		$table->char('hosting_box_id',50)->nullable()->default('NULL');
		$table->text('configuration_json');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
		$table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('domains');
    }
}