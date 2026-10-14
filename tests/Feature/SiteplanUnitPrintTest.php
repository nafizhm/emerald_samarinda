<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SiteplanUnitPrintTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->withoutMiddleware();
        foreach (['lokasi_kavling', 'kavling_peta', 'customer', 'transaksi_kavling', 'listrik_air', 'konfigurasi_media', 'marketing', 'list_penjualan', 'upload_file'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->increments('id');
                if ($name === 'lokasi_kavling') $table->string('nama_kavling');
                if ($name === 'kavling_peta') {
                    $table->integer('id_lokasi'); $table->string('kode_kavling');
                    $table->integer('hrg_jual'); $table->integer('luas_tanah'); $table->decimal('panjang')->default(10); $table->decimal('lebar')->default(6);
                }
                if ($name === 'customer') {
                    $table->string('nama_lengkap'); $table->integer('stt_arsip');
                    $table->integer('id_marketing')->nullable(); $table->integer('id_status_progres')->nullable();
                }
                if ($name === 'transaksi_kavling') {
                    $table->integer('id_customer'); $table->integer('id_kavling');
                    $table->string('tgl_terima')->nullable(); $table->integer('hrg_rumah')->nullable();
                }
                if ($name === 'listrik_air') $table->integer('id_kavling');
                if ($name === 'upload_file') $table->integer('id_customer');
                if ($name === 'konfigurasi_media') $table->string('jenis_data');
            });
        }
        DB::table('lokasi_kavling')->insert(['id' => 1, 'nama_kavling' => 'Lokasi Uji']);
        DB::table('kavling_peta')->insert(['id' => 7, 'id_lokasi' => 1, 'kode_kavling' => 'A/07', 'hrg_jual' => 150000000, 'luas_tanah' => 100]);
    }

    public function test_available_unit_can_be_printed_without_customer_fields(): void
    {
        $response = $this->post(route('st-penjualan.cetak'), ['id_kavling' => 7]);
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'inline; filename="Data_Unit_A_07.pdf"');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('%%EOF', $response->getContent());
    }

    public function test_print_uses_selected_unit_and_ignores_forged_form_values(): void
    {
        DB::table('customer')->insert(['id' => 2, 'nama_lengkap' => 'Pelanggan Aktif', 'stt_arsip' => 0]);
        DB::table('transaksi_kavling')->insert(['id_customer' => 2, 'id_kavling' => 7]);
        $response = $this->post(route('st-penjualan.cetak'), ['id_kavling' => 7, 'blok' => 'PALSU', 'tgl_lahir' => 'invalid', 'harga' => 1]);
        $response->assertOk()->assertHeader('Content-Disposition', 'inline; filename="Data_Unit_A_07.pdf"');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_print_rejects_missing_or_unknown_unit(): void
    {
        $this->postJson(route('st-penjualan.cetak'), [])->assertUnprocessable();
        $this->postJson(route('st-penjualan.cetak'), ['id_kavling' => 999])->assertNotFound();
        $this->getJson(route('st-penjualan.detail', 999))->assertNotFound();
    }

    public function test_detail_of_available_unit_has_no_customer(): void
    {
        $this->getJson(route('st-penjualan.detail', 7))->assertOk()
            ->assertJsonPath('kavling.id', 7)->assertJsonPath('customer', null);
        $this->getJson(route('st-penjualan.detail', 7))->assertJsonPath('kavling.panjang', 10)->assertJsonPath('kavling.lebar', 6);
    }
}
