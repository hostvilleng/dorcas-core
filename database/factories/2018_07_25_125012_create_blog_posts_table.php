<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->char('slug', 80)->unique();
            $table->unsignedInteger('company_id', false);
            $table->unsignedBigInteger('media_id', false)->nullable();
    
            $table->unsignedInteger("poster_id");
            $table->char("poster_type", 60);
            $table->index(["poster_id", "poster_type"], 'ix_poster');
            
            $table->char('title', 80);
            $table->string('summary', 1024)->nullable();
            $table->longText('content')->nullable();
            $table->boolean('is_published')->default(1);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('featured_at')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'title', 'is_published', 'publish_at', 'featured_at'], 'ix_blog_posts');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('media_id')->references('id')->on('blog_media')->onDelete('set null')->onUpdate('cascade');
        });
        
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog_posts');
    }
}
