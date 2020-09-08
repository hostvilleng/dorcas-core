<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePartnersWithExtraConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->text('extra_data')->nullable()->after('logo_url');
            $table->boolean('is_verified')->default(0)->after('extra_data');
            
            $table->dropIndex('parner_search_index');
            $table->index(['name', 'is_verified', 'created_at'], 'ix_partners');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('extra_data');
            $table->dropColumn('is_verified');
    
            $table->dropIndex('ix_partners');
            $table->index(['name', 'created_at'], 'ix_partners');
        });
    }
}
