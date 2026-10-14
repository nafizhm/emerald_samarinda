<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('konfigurasi_wa') || ! Schema::hasColumn('konfigurasi_wa', 'url_api')) {
            return;
        }

        DB::table('konfigurasi_wa')
            ->where('url_api', 'https://api.watzap.id/v1/send_message')
            ->update(['url_api' => '']);
    }

    public function down(): void
    {
        // No-op: the URL API should be controlled from the Pengaturan WA form.
    }
};
