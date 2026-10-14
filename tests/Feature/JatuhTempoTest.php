<?php

namespace Tests\Feature;

use App\Http\Controllers\JatuhTempoController;
use App\Models\User;
use App\Services\JatuhTempoService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JatuhTempoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Carbon::setTestNow(Carbon::parse('2026-10-13 12:00:00', 'Asia/Jakarta'));
        Schema::create('customer', function (Blueprint $table) {
            $table->increments('id'); $table->string('nama_lengkap'); $table->string('no_wa')->nullable(); $table->string('no_telp')->nullable();
            $table->integer('stt_arsip')->default(0); $table->double('inhouse_perbulan')->default(100);
            $table->integer('inhouse_tenor')->default(12); $table->date('inhouse_jatuh_tempo')->nullable();
        });
        Schema::create('lokasi_kavling', function (Blueprint $table) { $table->increments('id'); $table->string('nama_kavling'); });
        Schema::create('kavling_peta', function (Blueprint $table) {
            $table->increments('id'); $table->integer('id_lokasi'); $table->string('kode_kavling'); $table->double('hrg_jual')->default(1200);
        });
        Schema::create('transaksi_kavling', function (Blueprint $table) {
            $table->increments('id'); $table->integer('id_customer'); $table->integer('id_kavling');
            $table->date('tgl_terima')->nullable(); $table->double('hrg_rumah')->default(1200);
        });
        Schema::create('piutang', function (Blueprint $table) { $table->increments('id'); $table->integer('id_customer'); $table->double('nominal'); });
        Schema::create('pemasukan', function (Blueprint $table) { $table->increments('id'); $table->integer('id_customer'); $table->double('nominal'); $table->string('keterangan')->nullable(); });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function customer(string $name, ?string $date, array $extra = []): int
    {
        return DB::table('customer')->insertGetId($extra + ['nama_lengkap' => $name, 'inhouse_jatuh_tempo' => $date, 'no_wa' => '081234567890']);
    }

    private function payment(int $id, float $amount, ?string $description = null): void
    {
        DB::table('pemasukan')->insert(['id_customer' => $id, 'nominal' => $amount, 'keterangan' => $description]);
    }

    public function test_only_due_unpaid_active_customers_are_counted_once(): void
    {
        $today = $this->customer('Hari ini', '2026-10-13');
        $late = $this->customer('Terlambat', '2026-09-13');
        $this->customer('Besok', '2026-10-14');
        $paid = $this->customer('Lunas', '2026-09-13'); $this->payment($paid, 1200);
        $this->customer('Arsip', '2026-09-13', ['stt_arsip' => 1]);
        $current = $this->customer('Sudah bayar bulan ini', '2026-09-13'); $this->payment($current, 200);
        $this->customer('Belum diatur', null);
        $this->payment($late, 500, 'GANTI NAMA');
        $rows = (new JatuhTempoService)->rows();
        $this->assertCount(2, $rows);
        $this->assertSame([$late, $today], $rows->pluck('id')->all());
        $this->assertEquals(300, $rows->sum('due_amount'));
        $this->assertSame('Hari ini', $rows->last()['status']);
        $this->assertSame(30, $rows->first()['late_days']);
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $rows->first()['whatsapp_url']);
    }

    public function test_partial_payment_and_down_payment_do_not_hide_due_installments(): void
    {
        $partial = $this->customer('Sebagian', '2026-10-13'); $this->payment($partial, 50);
        $deposit = $this->customer('Dengan DP', '2026-09-13');
        DB::table('piutang')->insert(['id_customer' => $deposit, 'nominal' => 1500]);
        $this->payment($deposit, 300);
        $rows = (new JatuhTempoService)->rows()->keyBy('id');
        $this->assertEquals(50, $rows[$partial]['due_amount']);
        $this->assertEquals(200, $rows[$deposit]['due_amount']);
    }

    public function test_end_of_month_schedule_does_not_overflow(): void
    {
        $id = $this->customer('Akhir bulan', '2026-08-31'); $this->payment($id, 100);
        $row = (new JatuhTempoService)->rows()->first();
        $this->assertSame('2026-09-30', $row['due_date']->toDateString());
        $this->assertSame(13, $row['late_days']);
        $this->assertEquals(100, $row['due_amount']);
    }

    public function test_notification_count_and_filtered_page_use_same_source(): void
    {
        $this->customer('Budi', '2026-09-13'); $this->customer('Sari', '2026-10-13');
        $user = new User; $user->id = 1; $this->actingAs($user);
        $this->getJson(route('pelanggan-jatuh-tempo.notification'))->assertOk()->assertJson(['count' => 2]);
        $data = (new JatuhTempoController)->index(Request::create('/admin/pelanggan-jatuh-tempo', 'GET', ['status' => 'hari-ini', 'q' => 'Sari']), new JatuhTempoService)->getData();
        $this->assertSame(2, $data['summary']['total']);
        $this->assertSame(1, $data['customers']->total());
        $this->assertSame('Sari', $data['customers']->items()[0]['name']);
    }

    public function test_due_page_and_count_require_login(): void
    {
        $this->get(route('pelanggan-jatuh-tempo.index'))->assertRedirect(route('login'));
        $this->getJson(route('pelanggan-jatuh-tempo.notification'))->assertUnauthorized();
    }
}
