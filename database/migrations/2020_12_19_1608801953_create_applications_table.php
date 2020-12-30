<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',36);
		$table->integer('user_id',)->unsigned();
		$table->integer('oauth_client_id',)->unsigned()->nullable()->default('NULL');
		$table->char('name',80);
		$table->enum('type',['mobile','web','desktop','cli','others'])->default('web');
		$table->text('description');
		$table->string('homepage_url',400)->nullable()->default('NULL');
		$table->string('icon_filename',600)->nullable()->default('NULL');
		$table->string('banner_filename',600)->nullable()->default('NULL');
		$table->enum('billing_type',['one time','subscription'])->default('one time');
		$table->enum('billing_period',['weekly','monthly','yearly'])->nullable()->default('NULL');
		$table->char('billing_currency',3)->default('NGN');
		$table->decimal('billing_price',12,2)->default('0.00');
		$table->tinyInteger('is_published',1)->default('0');
		$table->tinyInteger('is_free',1)->default('1');
		$table->text('extra_json');
		$table->timestamp('published_at')->nullable()->default('NULL');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('oauth_client_id')->references('id')->on('oauth_clients');		$table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('applications');
    }
}