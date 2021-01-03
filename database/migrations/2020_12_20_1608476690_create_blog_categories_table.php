<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('blog_categories', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->char('uuid',36);
		$table->char('slug',80);
		$table->integer('company_id',)->unsigned();
		$table->bigInteger('parent_id',)->unsigned()->nullable()->default('NULL');
		$table->char('name',80);
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
		$table->primary('id');
		$table->foreign('company_id')->references('id')->on('companies');		$table->foreign('parent_id')->references('id')->on('blog_categories');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blog_categories');
    }
}