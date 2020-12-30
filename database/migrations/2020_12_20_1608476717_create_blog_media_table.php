<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogMediaTable extends Migration
{
    public function up()
    {
        Schema::create('blog_media', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',36);
		$table->integer('company_id',)->unsigned();
		$table->enum('type',['image','video'])->default('image');
		$table->char('title',80)->nullable()->default('NULL');
		$table->string('filename',300);
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blog_media');
    }
}