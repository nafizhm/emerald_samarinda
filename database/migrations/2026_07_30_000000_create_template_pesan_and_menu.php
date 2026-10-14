<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('template_pesan')) {
            Schema::create('template_pesan', function (Blueprint $table) {
                $table->id();
                $table->string('nama_template', 50);
                $table->string('jenis_pesan', 15)->unique();
                $table->text('isi_template');
            });
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

        $templateMenuId = DB::table('menu')
            ->where('route_name', 'template-pesan.index')
            ->value('id');

        if (! $templateMenuId) {
            $templateMenuId = DB::table('menu')->insertGetId([
                'id_parent'  => $pengaturanWaId,
                'title'      => 'Template Pesan',
                'route_name' => 'template-pesan.index',
                'icon'       => 'bi bi-chat-dots-fill',
                'urutan'     => 1,
                'lihat'      => 1,
                'tambah'     => 1,
                'edit'       => 1,
                'hapus'      => 1,
            ]);
        }

        if (! Schema::hasTable('hak_akses') || ! Schema::hasTable('users')) {
            return;
        }

        $userIds = DB::table('users')->pluck('id');
        foreach ($userIds as $userId) {
            foreach ([$pengaturanWaId, $templateMenuId] as $menuId) {
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
                        'tambah'  => $menuId == $templateMenuId ? 1 : 0,
                        'edit'    => $menuId == $templateMenuId ? 1 : 0,
                        'hapus'   => $menuId == $templateMenuId ? 1 : 0,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No-op: this migration may run on databases that already have these
        // records/tables from the bundled SQL dump.
    }
};
