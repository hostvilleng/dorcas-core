<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('blog_categories', function (Blueprint $table) {

            $table->integer('id')->primary()->unsigned();
            $table->char('uuid',36);
            $table->char('slug',80);
            $table->integer('company_id')->unsigned();
            $table->integer('parent_id')->unsigned()->nullable();
            $table->char('name',80);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies');	
            // $table->foreign('parent_id')->references('id')->on('blog_categories');

        });
    }

    public function down()
    {
        Schema::dropIfExists('blog_categories');
    }
}