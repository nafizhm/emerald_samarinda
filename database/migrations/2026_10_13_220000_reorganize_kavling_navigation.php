<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menu')) {
            return;
        }
        DB::transaction(function () {
            foreach (config('navigation') as $index => $item) {
                DB::table('menu')->where('id', $item['id'])->update([
                    'title' => $item['title'], 'urutan' => $index + 1, 'icon' => $item['icon'],
                ]);
                foreach ($item['children'] ?? [] as $route => $title) {
                    DB::table('menu')->where('route_name', $route)->update([
                        'id_parent' => $item['id'], 'title' => $title,
                        'urutan' => array_search($route, array_keys($item['children']), true) + 1,
                    ]);
                }
            }
            // Retain the legacy media permission record and URL for existing accounts.
            DB::table('menu')->where('route_name', 'pengaturan-media.index')
                ->update(['title' => 'Media Aplikasi', 'urutan' => 5]);
            DB::table('menu')->where('route_name', 'kavling.index')->update(['icon' => 'bi bi-bounding-box']);
            DB::table('menu')->where('route_name', 'pengaturanLanding.index')->update(['icon' => 'bi bi-globe']);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('menu')) {
            return;
        }
        $original = [
            2 => [0, 'Dashboard', 1, 'bi bi-grid-fill'], 3 => [0, 'Siteplan', 2, 'bi bi-map-fill'],
            4 => [0, 'Keuangan', 4, 'bi bi-cash-stack'], 5 => [0, 'Customer', 5, 'bi bi-people-fill'],
            7 => [0, 'Master Data', 7, 'bi bi-database-fill'], 8 => [0, 'Pengaturan', 8, 'bi bi-gear-fill'],
            11 => [5, 'Customer', 1], 12 => [5, 'Prospek', 2], 13 => [5, 'Upload File', 3], 14 => [5, 'Arsip Customer', 4],
            15 => [7, 'Marketing', 0], 16 => [7, 'Lokasi Kavling', 1], 17 => [7, 'Kavling', 2, 'bi bi-building-fill'], 18 => [7, 'Notaris', 3],
            19 => [8, 'Pengaturan Profile', 1], 20 => [8, 'Pengaturan Media', 2], 21 => [8, 'Hak Akses', 4], 22 => [8, 'Pengguna', 5],
            23 => [8, 'List Penjualan', 6], 27 => [0, 'Pembayaran', 3, 'bi bi-cart-fill'], 28 => [0, 'Legalitas', 6, 'bi bi-folder-fill'],
            29 => [8, 'pengaturan landing', 6, 'fas fa circle'], 30 => [4, 'Pemasukan', 1], 31 => [4, 'Pengeluaran', 2],
            32 => [4, 'Hutang', 3], 33 => [4, 'Piutang', 4], 34 => [4, 'Kategori Transaksi', 5], 35 => [4, 'Mutasi Saldo', 6],
            36 => [4, 'Laporan Arus Kas', 7], 37 => [8, 'Pengaturan Bank', 3], 38 => [0, 'Pengaturan WA', 9, 'bi bi-chat-dots-fill'],
            39 => [38, 'Template Pesan', 1], 40 => [38, 'Pengaturan Koneksi', 2],
        ];
        DB::transaction(function () use ($original) {
            foreach ($original as $id => $values) {
                $fields = ['id_parent' => $values[0], 'title' => $values[1], 'urutan' => $values[2]];
                if (isset($values[3])) {
                    $fields['icon'] = $values[3];
                }
                DB::table('menu')->where('id', $id)->update($fields);
            }
        });
    }
};
