<?php

namespace Tests\Feature;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('kavling_peta', function (Blueprint $table) { $table->increments('id'); $table->integer('id_lokasi'); });
        Schema::create('lokasi_kavling', function (Blueprint $table) {
            $table->increments('id'); $table->string('nama_kavling'); $table->string('nama_singkat')->default('');
            $table->integer('urutan'); $table->integer('stt_tampil')->default(1);
        });
        Schema::create('customer', function (Blueprint $table) {
            $table->increments('id'); $table->integer('stt_arsip')->default(0); $table->integer('id_status_progres');
            $table->integer('id_marketing')->default(1); $table->string('jenis_pembelian')->default('Kredit');
        });
        Schema::create('transaksi_kavling', function (Blueprint $table) { $table->increments('id'); $table->integer('id_customer'); $table->integer('id_kavling'); });
        Schema::create('progres_list_penjualan', function (Blueprint $table) {
            $table->increments('id'); $table->string('status_progres'); $table->string('short_name');
            $table->integer('urutan')->default(1); $table->integer('stt_tampil')->default(1);
        });
        Schema::create('marketing', function (Blueprint $table) { $table->increments('id'); $table->string('nama_marketing'); });
        $user = new User; $user->id = 1; $this->actingAs($user);
    }

    public function test_allocation_counts_unique_kavling_and_excludes_archived_customers(): void
    {
        DB::table('lokasi_kavling')->insert([
            ['id' => 1, 'nama_kavling' => 'SIRANDU 1', 'urutan' => 2],
            ['id' => 2, 'nama_kavling' => 'Lokasi B', 'urutan' => 1],
        ]);
        DB::table('kavling_peta')->insert([['id' => 1, 'id_lokasi' => 1], ['id' => 2, 'id_lokasi' => 1]]);
        DB::table('progres_list_penjualan')->insert(['id' => 77, 'status_progres' => 'HOLD', 'short_name' => 'Hold']);
        DB::table('customer')->insert([
            ['id' => 1, 'id_status_progres' => 77, 'stt_arsip' => 0],
            ['id' => 2, 'id_status_progres' => 77, 'stt_arsip' => 0],
            ['id' => 3, 'id_status_progres' => 77, 'stt_arsip' => 1],
        ]);
        DB::table('transaksi_kavling')->insert([
            ['id_customer' => 1, 'id_kavling' => 1], ['id_customer' => 2, 'id_kavling' => 1], ['id_customer' => 3, 'id_kavling' => 2],
        ]);
        $data = (new DashboardController)->index()->getData();
        $this->assertSame(2, $data['jumlahKavling']);
        $this->assertSame(1, $data['sudahLaku']);
        $this->assertSame(1, $data['sisaLaku']);
        $this->assertEquals(2, $data['dataLokasi']->first()['id']);
        $this->assertSame(1, $data['dataLokasi']->last()['hold']);
        $this->assertSame(1, $data['dataLokasi']->last()['kredit']);
    }

    public function test_empty_dashboard_has_zero_counts(): void
    {
        $data = (new DashboardController)->index()->getData();
        $this->assertSame(0, $data['jumlahKavling']);
        $this->assertSame(0, $data['sudahLaku']);
        $this->assertSame(0, $data['sisaLaku']);
        $this->assertTrue($data['dataLokasi']->isEmpty());
    }
}
