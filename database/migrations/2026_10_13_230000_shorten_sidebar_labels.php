<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menu')) return;
        DB::transaction(function () {
            DB::table('menu')->where('id', 5)->update(['title' => 'Pelanggan']);
            DB::table('menu')->where('route_name', 'pembayaran.index')->update(['title' => 'Pembayaran']);
            DB::table('menu')->where('route_name', 'mutasi-saldo.index')->update(['title' => 'Transfer Rekening']);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('menu')) return;
        DB::transaction(function () {
            DB::table('menu')->where('id', 5)->update(['title' => 'Penjualan & Pelanggan']);
            DB::table('menu')->where('route_name', 'pembayaran.index')->update(['title' => 'Pembayaran Pelanggan']);
            DB::table('menu')->where('route_name', 'mutasi-saldo.index')->update(['title' => 'Transfer Antar Rekening']);
        });
    }
};
