<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('konfigurasi_wa') && ! Schema::hasColumn('konfigurasi_wa', 'url_api')) {
            Schema::table('konfigurasi_wa', function (Blueprint $table) {
                $table->string('url_api')->default('https://api.watzap.id/v1/send_message')->after('id');
            });
        }

        if (Schema::hasTable('konfigurasi_wa')) {
            DB::table('konfigurasi_wa')
                ->whereNull('url_api')
                ->orWhere('url_api', '')
                ->update(['url_api' => 'https://api.watzap.id/v1/send_message']);

            if (DB::table('konfigurasi_wa')->count() === 0) {
                DB::table('konfigurasi_wa')->insert([
                    'url_api'       => 'https://api.watzap.id/v1/send_message',
                    'api_key'       => '',
                    'number_key'    => '',
                    'stt_keteangan' => 1,
                ]);
            }
        }

        if (! Schema::hasTable('menu')) {
            return;
        }

        $pengaturanWaId = DB::table('menu')
            ->where('route_name', '#')
            ->where('title', 'Pengaturan WA')
            ->value('id');

        if (! $pengaturanWaId) {
            $pengaturanWaId = DB::table('menu')->insertGetId([
                'id_parent'  => 0,
                'title'      => 'Pengaturan WA',
                'route_name' => '#',
                'icon'       => 'bi bi-chat-dots-fill',
                'urutan'     => 9,
                'lihat'      => 1,
                'tambah'     => 0,
                'edit'       => 0,
                'hapus'      => 0,
            ]);
        }

        DB::table('menu')
            ->where('route_name', 'template-pesan.index')
            ->update([
                'id_parent' => $pengaturanWaId,
                'title'     => 'Template Pesan',
                'urutan'    => 1,
                'lihat'     => 1,
                'tambah'    => 1,
                'edit'      => 1,
                'hapus'     => 1,
            ]);

        $koneksiMenuId = DB::table('menu')
            ->where('route_name', 'pengaturan-koneksi.index')
            ->value('id');

        if ($koneksiMenuId) {
            DB::table('menu')
                ->where('id', $koneksiMenuId)
                ->update([
                    'id_parent' => $pengaturanWaId,
                    'title'     => 'Pengaturan Koneksi',
                    'urutan'    => 2,
                    'lihat'     => 1,
                    'tambah'    => 0,
                    'edit'      => 1,
                    'hapus'     => 0,
                ]);
        } else {
            $koneksiMenuId = DB::table('menu')->insertGetId([
                'id_parent'  => $pengaturanWaId,
                'title'      => 'Pengaturan Koneksi',
                'route_name' => 'pengaturan-koneksi.index',
                'icon'       => 'bi bi-hdd-network-fill',
                'urutan'     => 2,
                'lihat'      => 1,
                'tambah'     => 0,
                'edit'       => 1,
                'hapus'      => 0,
            ]);
        }

        if (! Schema::hasTable('hak_akses') || ! Schema::hasTable('users')) {
            return;
        }

        $menuIds = DB::table('menu')
            ->whereIn('route_name', ['#', 'template-pesan.index', 'pengaturan-koneksi.index'])
            ->where(function ($query) {
                $query->where('title', 'Pengaturan WA')
                    ->orWhere('route_name', '!=', '#');
            })
            ->pluck('id');

        foreach (DB::table('users')->pluck('id') as $userId) {
            foreach ($menuIds as $menuId) {
                $exists = DB::table('hak_akses')
                    ->where('id_user', $userId)
                    ->where('id_menu', $menuId)
                    ->exists();

                if (! $exists) {
                    DB::table('hak_akses')->insert([
                        'id_user' => $userId,
                        'id_menu' => $menuId,
                        'lihat'   => 1,
                        'beranda' => 0,
                        'tambah'  => DB::table('menu')->where('id', $menuId)->value('tambah'),
                        'edit'    => DB::table('menu')->where('id', $menuId)->value('edit'),
                        'hapus'   => DB::table('menu')->where('id', $menuId)->value('hapus'),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No-op: menu rows and existing WA configuration may come from the SQL dump.
    }
};
