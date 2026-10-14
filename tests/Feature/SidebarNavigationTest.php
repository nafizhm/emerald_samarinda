<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SidebarNavigation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
        DB::purge('sqlite');
        Schema::create('menu', function (Blueprint $table) {
            $table->increments('id'); $table->integer('id_parent')->default(0); $table->string('title');
            $table->string('route_name'); $table->string('icon')->default(''); $table->integer('urutan')->default(1);
            foreach (['lihat', 'tambah', 'edit', 'hapus'] as $field) $table->integer($field)->default(1);
        });
        Schema::create('hak_akses', function (Blueprint $table) {
            $table->increments('id'); $table->integer('id_user'); $table->integer('id_menu');
            foreach (['lihat', 'beranda', 'tambah', 'edit', 'hapus'] as $field) $table->integer($field)->default(0);
        });
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id'); $table->string('username'); $table->string('password');
            $table->string('status')->default('AKTIF'); $table->string('remember_token')->nullable();
        });
        DB::table('users')->insert(['id' => 1, 'username' => 'tester', 'password' => Hash::make('valid-password')]);
        foreach (config('navigation') as $item) {
            DB::table('menu')->insert(['id' => $item['id'], 'title' => $item['title'], 'route_name' => $item['route'] ?? '#']);
        }
        $id = 100;
        foreach (config('navigation') as $item) {
            foreach ($item['children'] ?? [] as $route => $title) {
                DB::table('menu')->insert(['id' => $id++, 'id_parent' => $item['id'], 'title' => $title, 'route_name' => $route]);
            }
        }
        DB::table('menu')->insert(['id' => 200, 'id_parent' => 8, 'title' => 'Legacy media', 'route_name' => 'pengaturan-media.index']);
    }

    private function grant(string $route, array $fields = []): int
    {
        return DB::table('hak_akses')->insertGetId(['id_user' => 1, 'id_menu' => DB::table('menu')->where('route_name', $route)->value('id'), 'lihat' => 1] + $fields);
    }

    public function test_sidebar_follows_workflow_and_child_permissions_without_stale_session(): void
    {
        foreach (['dashboard', 'st-penjualan.index', 'customer.index', 'upload-file.index', 'bank.index', 'list-penjualan.index'] as $route) $this->grant($route);
        $items = (new SidebarNavigation)->forUser(1, 'customer.edit');
        $this->assertSame(['Dashboard', 'Denah Penjualan', 'Pelanggan', 'Keuangan', 'Data Referensi'], array_column($items, 'title'));
        $this->assertSame(['Data Pelanggan', 'Dokumen Pelanggan'], array_column($items[2]['children'], 'title'));
        $this->assertTrue($items[2]['active']);
        $this->assertSame('Rekening Bank', $items[3]['children'][0]['title']);
        $this->assertSame('Tahapan Penjualan', $items[4]['children'][0]['title']);
        DB::table('hak_akses')->where('id_menu', DB::table('menu')->where('route_name', 'customer.index')->value('id'))->update(['lihat' => 0]);
        $items = (new SidebarNavigation)->forUser(1, 'customer.edit');
        $this->assertSame(['Dokumen Pelanggan'], array_column($items[2]['children'], 'title'));
        $this->assertFalse($items[2]['active']);
    }

    public function test_media_only_permission_has_one_combined_settings_link(): void
    {
        $this->grant('pengaturan-media.index');
        $items = (new SidebarNavigation)->forUser(1, 'pengaturan-profile.index');
        $this->assertSame('Profil & Media', $items[0]['children'][0]['title']);
        $this->assertTrue($items[0]['active']);
        $this->grant('pengaturan-profile.index');
        $items = (new SidebarNavigation)->forUser(1, 'pengaturan-profile.index');
        $this->assertCount(1, $items[0]['children']);
    }

    public function test_empty_groups_and_secret_upload_are_absent(): void
    {
        $this->assertSame([], (new SidebarNavigation)->forUser(1, 'uploaddenah.index'));
        $this->grant('st-penjualan.index');
        $this->assertTrue((new SidebarNavigation)->forUser(1, 'denah-penjualan.cetak.pdf')[0]['active']);
    }

    public function test_blocked_account_and_invalid_password_never_create_session(): void
    {
        $this->postJson(route('admin.loginPost'), ['username' => 'tester', 'password' => 'incorrect-password'])->assertUnprocessable();
        $this->assertGuest();
        DB::table('users')->where('id', 1)->update(['status' => 'BLOKIR']);
        $this->postJson(route('admin.loginPost'), ['username' => 'tester', 'password' => 'valid-password'])->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_normal_password_login_still_works(): void
    {
        $this->postJson(route('admin.loginPost'), ['username' => 'tester', 'password' => 'valid-password'])->assertOk();
        $this->assertAuthenticated();
    }

    public function test_partial_permission_change_preserves_other_fields_and_users(): void
    {
        $this->grant('hak-akses.index', ['edit' => 1]);
        $row = $this->grant('customer.index', ['edit' => 1, 'tambah' => 1]);
        $other = DB::table('hak_akses')->insertGetId(['id_user' => 2, 'id_menu' => 3, 'lihat' => 1]);
        $this->actingAs(User::find(1));
        $this->putJson(route('admin.updateHakAkses'), ['id_user' => 1, 'hak_akses_data' => ['edit' => [$row => 0], 'lihat' => [$other => 0]]])->assertOk();
        $this->assertEquals(1, DB::table('hak_akses')->where('id', $row)->value('tambah'));
        $this->assertEquals(1, DB::table('hak_akses')->where('id', $row)->value('lihat'));
        $this->assertEquals(0, DB::table('hak_akses')->where('id', $row)->value('edit'));
        $this->assertEquals(1, DB::table('hak_akses')->where('id', $other)->value('lihat'));
    }

    public function test_permission_update_requires_editor_permission(): void
    {
        $this->actingAs(User::find(1));
        $this->putJson(route('admin.updateHakAkses'), ['id_user' => 1, 'hak_akses_data' => []])->assertForbidden();
    }

    public function test_permission_table_requires_access_even_if_client_claims_editor_permission(): void
    {
        $this->actingAs(User::find(1));
        $this->getJson(route('admin.getHakAkses', ['id_user' => 1, 'permissions' => ['edit' => 1]]))->assertForbidden();
    }

    public function test_migration_reorders_without_changing_existing_permissions(): void
    {
        $this->grant('bank.index', ['edit' => 1]);
        $before = DB::table('hak_akses')->get()->toJson();
        $migration = require base_path('database/migrations/2026_10_13_220000_reorganize_kavling_navigation.php');
        $migration->up();
        $this->assertEquals(4, DB::table('menu')->where('route_name', 'bank.index')->value('id_parent'));
        $this->assertEquals(7, DB::table('menu')->where('route_name', 'list-penjualan.index')->value('id_parent'));
        $this->assertSame($before, DB::table('hak_akses')->get()->toJson());
    }
}
