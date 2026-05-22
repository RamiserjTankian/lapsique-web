<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, contar cuántos duplicados hay por link para actualizar el contador después
        $duplicatesByLink = DB::table('guest_list_entries as g1')
            ->join('guest_list_entries as g2', function($join) {
                $join->on('g1.invite_link_id', '=', 'g2.invite_link_id')
                     ->on('g1.customer_id', '=', 'g2.customer_id')
                     ->whereColumn('g1.id', '<', 'g2.id');
            })
            ->whereNull('g1.deleted_at')
            ->whereNull('g2.deleted_at')
            ->select('g1.invite_link_id', DB::raw('COUNT(*) as duplicates'))
            ->groupBy('g1.invite_link_id')
            ->get();

        // Eliminar registros duplicados manteniendo solo el más reciente
        // para cada combinación de invite_link_id y customer_id
        $duplicateIds = DB::table('guest_list_entries as g1')
            ->join('guest_list_entries as g2', function ($join) {
                $join->on('g1.invite_link_id', '=', 'g2.invite_link_id')
                    ->on('g1.customer_id', '=', 'g2.customer_id')
                    ->whereColumn('g1.id', '<', 'g2.id');
            })
            ->whereNull('g1.deleted_at')
            ->whereNull('g2.deleted_at')
            ->pluck('g1.id');

        DB::table('guest_list_entries')
            ->whereIn('id', $duplicateIds)
            ->delete();

        // Actualizar el contador de registros en los links afectados
        foreach ($duplicatesByLink as $duplicate) {
            DB::table('guest_list_invite_links')
                ->where('id', $duplicate->invite_link_id)
                ->decrement('current_registrations', $duplicate->duplicates);
        }

        // Agregar índice único compuesto para prevenir duplicados futuros
        Schema::table('guest_list_entries', function (Blueprint $table) {
            $table->unique(['invite_link_id', 'customer_id'], 'unique_invite_link_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_list_entries', function (Blueprint $table) {
            $table->dropUnique('unique_invite_link_customer');
        });
    }
};
