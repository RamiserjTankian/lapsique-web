<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category', 50)->default('ticket')->index();
            $table->string('currency', 3)->default('MXN');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('access_units')->default(1)->comment('Cuantos accesos requiere por unidad.');
            $table->unsignedInteger('check_in_limit')->default(1)->comment('Usos permitidos por QR.');
            $table->unsignedInteger('stock')->nullable()->comment('Unidades disponibles (null = ilimitado).');
            $table->unsignedInteger('reserved_count')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->unsignedInteger('max_per_order')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_products');
    }
};
