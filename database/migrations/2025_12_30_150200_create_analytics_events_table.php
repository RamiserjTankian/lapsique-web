<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analytics_session_id')->constrained('analytics_sessions')->cascadeOnDelete();
            $table->foreignId('analytics_pageview_id')->nullable()->constrained('analytics_pageviews')->nullOnDelete();
            $table->uuid('visitor_id')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 50);
            $table->string('category', 50)->nullable();
            $table->string('label', 255)->nullable();
            $table->integer('value')->nullable();
            $table->text('url')->nullable();
            $table->string('path', 255)->nullable();
            $table->string('element_tag', 50)->nullable();
            $table->string('element_text', 255)->nullable();
            $table->text('element_href')->nullable();
            $table->string('element_id', 100)->nullable();
            $table->text('element_classes')->nullable();
            $table->string('element_target', 30)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
