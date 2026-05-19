<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_sessions', function (Blueprint $table) {
            $table->string('source_type', 40)->nullable()->after('utm_content');
            $table->string('source_label', 255)->nullable()->after('source_type');
            $table->string('country_name', 120)->nullable()->after('country');
            $table->string('region_code', 40)->nullable()->after('region');

            $table->index(['source_type', 'created_at'], 'analytics_sessions_source_type_created_at_index');
            $table->index(['country', 'region_code'], 'analytics_sessions_country_region_code_index');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_sessions', function (Blueprint $table) {
            $table->dropIndex('analytics_sessions_source_type_created_at_index');
            $table->dropIndex('analytics_sessions_country_region_code_index');

            $table->dropColumn([
                'source_type',
                'source_label',
                'country_name',
                'region_code',
            ]);
        });
    }
};
