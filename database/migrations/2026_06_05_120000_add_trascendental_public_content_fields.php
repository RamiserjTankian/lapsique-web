<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('trascendental_kind')->nullable()->after('is_case_study')->index();
            $table->boolean('trascendental_visible')->default(false)->after('trascendental_kind')->index();
            $table->string('lineup_text')->nullable()->after('description');
            $table->string('public_image_path')->nullable()->after('ticket_url');
            $table->string('source_url')->nullable()->after('public_image_path');
            $table->string('details_url')->nullable()->after('source_url');
        });

        Schema::table('djs', function (Blueprint $table) {
            $table->boolean('trascendental_roster')->default(false)->after('is_featured')->index();
            $table->string('booking_status')->nullable()->after('trascendental_roster');
            $table->string('nationality')->nullable()->after('booking_status');
            $table->string('record_label')->nullable()->after('nationality');
            $table->string('public_image_path')->nullable()->after('website_url');
            $table->string('instagram_url')->nullable()->after('instagram_handle');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'trascendental_kind',
                'trascendental_visible',
                'lineup_text',
                'public_image_path',
                'source_url',
                'details_url',
            ]);
        });

        Schema::table('djs', function (Blueprint $table) {
            $table->dropColumn([
                'trascendental_roster',
                'booking_status',
                'nationality',
                'record_label',
                'public_image_path',
                'instagram_url',
            ]);
        });
    }
};
