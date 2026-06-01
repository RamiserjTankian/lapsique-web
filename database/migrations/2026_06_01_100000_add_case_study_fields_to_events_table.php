<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_case_study')->default(false)->after('is_featured');
            $table->string('case_summary')->nullable()->after('is_case_study');
            $table->json('case_metrics')->nullable()->after('case_summary');
            $table->json('case_services')->nullable()->after('case_metrics');
            $table->unsignedInteger('case_sort')->default(0)->after('case_services');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'is_case_study',
                'case_summary',
                'case_metrics',
                'case_services',
                'case_sort',
            ]);
        });
    }
};
