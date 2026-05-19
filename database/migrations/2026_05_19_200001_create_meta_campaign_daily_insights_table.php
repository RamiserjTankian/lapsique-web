<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_campaign_daily_insights', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('campaign_id', 64);
            $table->string('campaign_name')->nullable();
            $table->decimal('spend', 12, 2)->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->decimal('cpc', 12, 4)->nullable();
            $table->decimal('cpm', 12, 4)->nullable();
            $table->json('actions')->nullable();
            $table->json('cost_per_action_type')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['date', 'campaign_id']);
            $table->index('campaign_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_campaign_daily_insights');
    }
};
