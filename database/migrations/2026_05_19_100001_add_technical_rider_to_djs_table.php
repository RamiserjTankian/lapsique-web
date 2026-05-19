<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('djs', function (Blueprint $table) {
            $table->json('technical_rider')->nullable()->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('djs', function (Blueprint $table) {
            $table->dropColumn('technical_rider');
        });
    }
};
