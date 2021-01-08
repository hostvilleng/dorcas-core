<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {

		$table->increments('id');
		$table->char('uuid',50);
		$table->char('name',80);
		$table->char('slug',50);
		$table->string('logo_url',600)->nullable();
		$table->text('extra_data');
		$table->tinyInteger('is_verified');
		$table->timestamp('deleted_at')->nullable();
		$table->timestamps();
		// $table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('partners');
    }
}