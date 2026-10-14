<?php

namespace Tests\Feature;

use App\Models\{Bank, Customer, KavlingPeta, ListPenjualan, LokasiKavling, Marketing, Pemasukan, PersyaratanLegal, Piutang, TransaksiKavling};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CustomerPurchaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->withoutMiddleware();
        foreach ([Bank::class, Customer::class, KavlingPeta::class, ListPenjualan::class, LokasiKavling::class, Marketing::class, Pemasukan::class, PersyaratanLegal::class, Piutang::class, TransaksiKavling::class] as $class) {
            $model = new $class;
            Schema::create($model->getTable(), function (Blueprint $table) use ($model) {
                $table->increments('id');
                foreach ($model->getFillable() as $field) {
                    if ($field === 'id') continue;
                    if ($field === 'stt_arsip') $table->integer($field)->default(0);
                    elseif ($field === 'email') $table->string($field, 75);
                    else $table->text($field)->nullable();
                }
            });
        }
        DB::table('lokasi_kavling')->insert(['id' => 1, 'nama_kavling' => 'Lokasi Uji', 'nama_singkat' => 'UJI']);
        DB::table('lokasi_kavling')->insert(['id' => 2, 'nama_kavling' => 'Lokasi Lain']);
        DB::table('bank')->insert(['id' => 1]);
        DB::table('progres_list_penjualan')->insert(['id' => 1, 'status_progres' => 'Booking']);
        DB::table('kavling_peta')->insert([
            ['id' => 1, 'id_lokasi' => 1, 'kode_kavling' => 'A1', 'hrg_jual' => 100000000],
            ['id' => 2, 'id_lokasi' => 1, 'kode_kavling' => 'A2', 'hrg_jual' => 200000000],
            ['id' => 3, 'id_lokasi' => 2, 'kode_kavling' => 'B1', 'hrg_jual' => 50000000],
        ]);
    }

    protected function purchase(array $overrides = []): array
    {
        return array_replace([
            'tgl_terima' => '2026-10-13', 'nama_lengkap' => 'Pembeli Uji',
            'no_ktp' => '0012345678901234', 'tempat_lahir' => 'Balikpapan',
            'tgl_lahir' => '1990-01-01', 'no_telp' => '081234567890', 'alamat' => 'Alamat Uji',
            'id_lokasi' => 1, 'id_kavling' => [1, 2], 'id_status_progres' => 1,
            'id_bank' => 1, 'hrg_jual' => '1', 'discount' => '1.001',
            'jenis_pembelian' => 'Booking', 'pembayaran_booking' => '1.000.000',
            'tgl_batas_booking' => '2026-10-20',
        ], $overrides);
    }

    public function test_booking_creates_units_receivable_and_payment_with_exact_discount(): void
    {
        $this->postJson(route('customer.store'), $this->purchase())->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseCount('customer', 1);
        $this->assertDatabaseCount('transaksi_kavling', 2);
        $this->assertDatabaseHas('customer', ['no_ktp' => '0012345678901234', 'no_telp' => '081234567890']);
        $this->assertDatabaseHas('piutang', ['nominal' => 299998999, 'terbayar' => 1000000, 'sisa_bayar' => 298998999]);
        $this->assertDatabaseHas('pemasukan', ['nominal' => 1000000]);
    }

    public function test_cash_purchase_records_full_payment(): void
    {
        $this->postJson(route('customer.store'), $this->purchase(['jenis_pembelian' => 'Cash Keras']))->assertOk();
        $this->assertDatabaseHas('piutang', ['nominal' => 299998999, 'sisa_bayar' => 0, 'status' => 2]);
        $this->assertDatabaseHas('pemasukan', ['nominal' => 299998999]);
    }

    public function test_credit_requires_positive_tenor_and_records_dp(): void
    {
        $input = $this->purchase(['jenis_pembelian' => 'Kredit', 'dp_kredit' => 10000000, 'inhouse_tenor' => 0, 'inhouse_perbulan' => 5000000, 'inhouse_jatuh_tempo' => '2026-11-13']);
        $this->postJson(route('customer.store'), $input)->assertUnprocessable()->assertJsonValidationErrors('inhouse_tenor');
        $input['inhouse_tenor'] = 60;
        $this->postJson(route('customer.store'), $input)->assertOk();
        $this->assertDatabaseHas('piutang', ['terbayar' => 10000000, 'sisa_bayar' => 289998999]);
    }

    public function test_purchase_rejects_unavailable_or_wrong_location_units_without_partial_records(): void
    {
        $this->postJson(route('customer.store'), $this->purchase(['id_kavling' => [3]]))->assertUnprocessable()->assertJsonValidationErrors('id_kavling');
        $this->assertDatabaseCount('customer', 0);
        $this->postJson(route('customer.store'), $this->purchase())->assertOk();
        $this->postJson(route('customer.store'), $this->purchase())->assertUnprocessable()->assertJsonValidationErrors('id_kavling');
        $this->assertDatabaseCount('customer', 1);
        $this->assertDatabaseCount('piutang', 1);
    }

    public function test_purchase_rejects_duplicates_excess_discount_and_overpayment(): void
    {
        $this->postJson(route('customer.store'), $this->purchase(['id_kavling' => [1, 1]]))->assertUnprocessable();
        $this->postJson(route('customer.store'), $this->purchase(['discount' => 400000000]))->assertUnprocessable()->assertJsonValidationErrors('discount');
        $this->postJson(route('customer.store'), $this->purchase(['pembayaran_booking' => 400000000]))->assertUnprocessable();
        $this->assertDatabaseCount('customer', 0);
        $this->assertDatabaseCount('pemasukan', 0);
    }

    public function test_prospect_routes_and_navigation_are_removed(): void
    {
        $this->assertFalse(Route::has('prospek.index'));
        $this->assertFalse(Route::has('prospekData.createUtj'));
        $customerMenu = collect(config('navigation'))->firstWhere('id', 5);
        $this->assertArrayNotHasKey('prospek.index', $customerMenu['children']);
    }
}
