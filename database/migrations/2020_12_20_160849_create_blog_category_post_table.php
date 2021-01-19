<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogCategoryPostTable extends Migration
{
    public function up()
    {
        Schema::create('blog_category_post', function (Blueprint $table) {

		$table->integer('blog_category_id')->unsigned();
		$table->integer('blog_post_id')->unsigned();
		$table->primary(['blog_category_id','blog_post_id']);
        $table->foreign('blog_category_id')->references('id')->on('blog_categories');	
        $table->foreign('blog_post_id')->references('id')->on('blog_posts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blog_category_post');
    }
}