<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_visitor_identities', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_id')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('source', 80)->nullable();
            $table->timestamp('first_linked_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'last_seen_at']);
        });

        Schema::table('analytics_sessions', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();
            $table->index(['customer_id', 'created_at'], 'analytics_sessions_customer_created_index');
        });

        Schema::table('analytics_pageviews', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();
            $table->index(['customer_id', 'created_at'], 'analytics_pageviews_customer_created_index');
        });

        Schema::table('analytics_events', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();
            $table->index(['customer_id', 'created_at'], 'analytics_events_customer_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropIndex('analytics_events_customer_created_index');
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::table('analytics_pageviews', function (Blueprint $table) {
            $table->dropIndex('analytics_pageviews_customer_created_index');
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::table('analytics_sessions', function (Blueprint $table) {
            $table->dropIndex('analytics_sessions_customer_created_index');
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::dropIfExists('analytics_visitor_identities');
    }
};
