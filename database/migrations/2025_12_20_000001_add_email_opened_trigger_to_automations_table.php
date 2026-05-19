<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE automations MODIFY trigger_type ENUM(
                    'signup',
                    'event_registration',
                    'event_reminder',
                    'abandoned_cart',
                    'birthday',
                    'anniversary',
                    'tag_added',
                    'lifecycle_change',
                    'score_threshold',
                    'email_opened'
                ) NOT NULL"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE automations MODIFY trigger_type ENUM(
                    'signup',
                    'event_registration',
                    'event_reminder',
                    'abandoned_cart',
                    'birthday',
                    'anniversary',
                    'tag_added',
                    'lifecycle_change',
                    'score_threshold'
                ) NOT NULL"
            );
        }
    }
};
