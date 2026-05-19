<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('fiscal_legal_name')->nullable()->after('notes');
            $table->string('fiscal_rfc', 13)->nullable()->after('fiscal_legal_name');
            $table->string('fiscal_regime')->nullable()->after('fiscal_rfc');
            $table->string('fiscal_cfdi_use', 10)->nullable()->after('fiscal_regime');
            $table->string('fiscal_email')->nullable()->after('fiscal_cfdi_use');
            $table->string('fiscal_zip', 10)->nullable()->after('fiscal_email');
            $table->string('fiscal_address')->nullable()->after('fiscal_zip');
            $table->string('fiscal_city')->nullable()->after('fiscal_address');
            $table->string('fiscal_state')->nullable()->after('fiscal_city');
            $table->string('fiscal_country', 2)->default('MX')->after('fiscal_state');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'fiscal_legal_name',
                'fiscal_rfc',
                'fiscal_regime',
                'fiscal_cfdi_use',
                'fiscal_email',
                'fiscal_zip',
                'fiscal_address',
                'fiscal_city',
                'fiscal_state',
                'fiscal_country',
            ]);
        });
    }
};
