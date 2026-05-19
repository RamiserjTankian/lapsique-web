<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_products', function (Blueprint $table) {
            $table->decimal('service_charge_pct', 5, 2)->default(0)->after('price')
                ->comment('Porcentaje de cargo de servicio (ej. 15 = 15%). 0 = sin cargo.');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_products', function (Blueprint $table) {
            $table->dropColumn('service_charge_pct');
        });
    }
};
