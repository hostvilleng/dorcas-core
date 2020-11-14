<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adverts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('company_id', false);
            $table->unsignedInteger('poster_id', false);
            $table->char('type', 50)->default('sidebar');
            $table->char('title', 80)->nullable();
            $table->char('image_filename', 255)->nullable();
            $table->string('redirect_url', 500)->nullable();
            $table->text('extra_data')->nullable();
            $table->boolean('is_default')->default(0);
            $table->timestamps();
            
            $table->index(['company_id', 'poster_id', 'type', 'title', 'is_default', 'created_at'], 'ix_adverts');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('poster_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adverts');
    }
}
