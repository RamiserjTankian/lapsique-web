<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('site', 40)->default('trascendental')->index();
            $table->string('page', 80)->index();
            $table->string('section', 80)->index();
            $table->string('key', 80)->index();
            $table->string('locale', 8)->default('en')->index();
            $table->string('eyebrow')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('asset_path')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('priority')->default(0);
            $table->timestamps();

            $table->unique(['site', 'page', 'section', 'key', 'locale'], 'page_content_blocks_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_content_blocks');
    }
};
