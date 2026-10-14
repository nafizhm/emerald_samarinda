<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menu')) {
            $ids = DB::table('menu')->where('route_name', 'like', 'prospek%')->pluck('id');
            DB::transaction(function () use ($ids) {
                if (Schema::hasTable('hak_akses')) {
                    DB::table('hak_akses')->whereIn('id_menu', $ids)->delete();
                }
                DB::table('menu')->whereIn('id', $ids)->delete();
            });
        }
        Schema::dropIfExists('prospek_nasabah');
    }

    public function down(): void
    {
        // This removal cannot recover prospect records or menu permissions.
        throw new RuntimeException('Penghapusan modul Prospek tidak dapat dibatalkan otomatis. Pulihkan dari cadangan bila diperlukan.');
    }
};
