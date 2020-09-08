<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('user_id', false);
            $table->unsignedInteger('oauth_client_id', false)->nullable();
            $table->char('name', 80);
            $table->enum('type', ['mobile', 'web', 'desktop', 'cli', 'others'])->default('web');
            $table->text('description');
            $table->string('homepage_url', 400)->nullable();
            $table->string('icon_filename', 600)->nullable();
            $table->string('banner_filename', 600)->nullable();
            $table->enum('billing_type', ['one time', 'subscription'])->default('one time');
            $table->enum('billing_period', ['weekly', 'monthly', 'yearly'])->nullable();
            $table->char('billing_currency', 3)->default('NGN');
            $table->unsignedDecimal('billing_price', 12, 2)->default(0.00);
            $table->boolean('is_published')->default(0);
            $table->boolean('is_free')->default(1);
            $table->text('extra_json')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
    
            $table->index(['user_id', 'oauth_client_id', 'name', 'type', 'billing_type', 'billing_period', 'billing_currency', 'billing_price', 'is_published', 'is_free'], 'ix_application_installs');
    
            $table->foreign('oauth_client_id')->references('id')->on('oauth_clients')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    
        Schema::create('application_category', function (Blueprint $table) {
            $table->unsignedBigInteger('application_id', false);
            $table->unsignedInteger('application_category_id', false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('application_category');
        Schema::dropIfExists('applications');
    }
}
