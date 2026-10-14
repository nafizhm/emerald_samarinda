<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KavlingModalUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->withoutMiddleware();
        Schema::create('menu', function (Blueprint $t) {$t->increments('id'); $t->string('route_name');});
        Schema::create('hak_akses', function (Blueprint $t) {
            $t->increments('id'); $t->integer('id_user'); $t->integer('id_menu');
            $t->integer('edit'); $t->integer('tambah')->default(0); $t->integer('hapus')->default(0);
        });
        Schema::create('lokasi_kavling', function (Blueprint $t) {$t->increments('id'); $t->string('nama_kavling');});
        Schema::create('kavling_peta', function (Blueprint $t) {
            $t->increments('id'); $t->integer('id_lokasi'); $t->string('kode_kavling');
            foreach (['panjang', 'lebar', 'luas_tanah', 'hrg_jual'] as $field) $t->decimal($field)->default(1);
            $t->text('keterangan')->default('');
            $t->string('jenis_map')->default('polygon'); $t->text('map')->default('POINTS-ASLI'); $t->string('matrik')->default('MATRIK-ASLI');
        });
        DB::table('menu')->insert(['id' => 1, 'route_name' => 'kavling.index']);
        DB::table('hak_akses')->insert(['id_user' => 1, 'id_menu' => 1, 'edit' => 1]);
        DB::table('lokasi_kavling')->insert([['id' => 1, 'nama_kavling' => 'Proyek A'], ['id' => 2, 'nama_kavling' => 'Proyek B']]);
        DB::table('kavling_peta')->insert([
            ['id' => 1, 'id_lokasi' => 1, 'kode_kavling' => 'A1'],
            ['id' => 2, 'id_lokasi' => 1, 'kode_kavling' => 'A2'],
            ['id' => 3, 'id_lokasi' => 2, 'kode_kavling' => 'B1'],
        ]);
        $user = new User; $user->id = 1; $this->actingAs($user);
    }

    private function values(): array
    {
        return ['panjang' => 10.5, 'lebar' => 6,
            'luas_tanah' => 60, 'hrg_jual' => '90.000.000', 'keterangan' => 'Uji'];
    }

    public function test_edit_returns_modal_data_and_old_page_redirects(): void
    {
        $this->getJson(route('kavling.edit', 1))->assertOk()->assertJsonPath('data.kode_kavling', 'A1')->assertJsonPath('project_count', 2);
        $this->get(route('kavling.edit', 1))->assertRedirect(route('kavling.index', ['edit' => 1]));
    }

    public function test_unchecked_updates_selected_unit_only(): void
    {
        $this->putJson(route('kavling.update', 1), $this->values())->assertOk()->assertJsonPath('updated_count', 1);
        $this->assertDatabaseHas('kavling_peta', ['id' => 1, 'hrg_jual' => 90000000, 'map' => 'POINTS-ASLI', 'panjang' => 10.5, 'lebar' => 6]);
        $this->assertDatabaseHas('kavling_peta', ['id' => 2, 'hrg_jual' => 1]);
    }

    public function test_bulk_update_uses_original_project_and_preserves_unit_codes(): void
    {
        $this->putJson(route('kavling.update', 1), $this->values() + ['update_project' => 1, 'id_lokasi' => 2, 'kode_kavling' => 'PALSU'])
            ->assertOk()->assertJsonPath('updated_count', 2);
        $this->assertDatabaseHas('kavling_peta', ['id' => 2, 'hrg_jual' => 90000000, 'kode_kavling' => 'A2']);
        $this->assertDatabaseHas('kavling_peta', ['id' => 3, 'hrg_jual' => 1]);
    }

    public function test_invalid_price_does_not_update_any_unit(): void
    {
        $values = $this->values(); $values['hrg_jual'] = 'abc'; $values['update_project'] = 1;
        $this->putJson(route('kavling.update', 1), $values)->assertUnprocessable()->assertJsonValidationErrors('hrg_jual');
        $this->assertSame(3, DB::table('kavling_peta')->where('hrg_jual', 1)->count());
    }

    public function test_edit_permission_is_required_for_bulk_update(): void
    {
        DB::table('hak_akses')->update(['edit' => 0]);
        $this->getJson(route('kavling.edit', 1))->assertForbidden();
        $this->putJson(route('kavling.update', 1), $this->values() + ['update_project' => 1])->assertForbidden();
        $this->assertSame(3, DB::table('kavling_peta')->where('hrg_jual', 1)->count());
    }
}
