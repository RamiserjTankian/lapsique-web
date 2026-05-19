<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_charges', function (Blueprint $table) {
            $table->foreignId('ayb_product_id')->nullable()->after('user_id')->constrained('ayb_products')->nullOnDelete();
            $table->string('item_type', 20)->nullable()->after('item_name');
        });
    }

    public function down(): void
    {
        Schema::table('pos_charges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ayb_product_id');
            $table->dropColumn('item_type');
        });
    }
};
