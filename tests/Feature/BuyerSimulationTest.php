<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JatuhTempoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BuyerSimulationTest extends CustomerPurchaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-14 12:00:00', 'Asia/Jakarta'));
        $user = new User; $user->id = 1; $this->actingAs($user);
    }

    protected function tearDown(): void { Carbon::setTestNow(); parent::tearDown(); }

    private function pay(int $customer, int $amount): void
    {
        $id = DB::table('piutang')->where('id_customer', $customer)->value('id');
        $this->postJson(route('pemasukan.store'), ['tanggal' => '2026-10-14', 'nominal' => number_format($amount, 0, ',', '.'),
            'id_kategori_transaksi' => 6, 'id_piutang' => $id, 'id_bank' => 1, 'keterangan' => 'Cicilan simulasi'])
            ->assertOk();
    }

    public function test_thirty_buyers_cash_credit_paid_partial_and_one_to_six_months_overdue(): void
    {
        $plans = array_merge(array_fill(0, 6, ['kind' => 'cash']), array_fill(0, 3, ['kind' => 'current']), array_fill(0, 3, ['kind' => 'paid']));
        for ($month = 1; $month <= 6; $month++) foreach (['unpaid', 'partial', 'installment'] as $variant) $plans[] = ['kind' => 'late', 'months' => $month, 'variant' => $variant];
        $expected = []; $paidIds = [];
        foreach ($plans as $index => $plan) {
            $unit = 101 + $index;
            DB::table('kavling_peta')->insert(['id' => $unit, 'id_lokasi' => 1, 'kode_kavling' => 'SIM-'.($index + 1), 'hrg_jual' => 120000000]);
            $firstDue = $plan['kind'] === 'late' ? Carbon::parse('2026-10-15')->subMonthsNoOverflow($plan['months'])->toDateString() : ($plan['kind'] === 'paid' ? '2026-06-15' : '2026-11-14');
            $input = $this->purchase(['nama_lengkap' => 'SIMULASI '.str_pad($index + 1, 2, '0', STR_PAD_LEFT), 'id_kavling' => [$unit],
                'no_ktp' => '990000000000'.str_pad($index + 1, 4, '0', STR_PAD_LEFT), 'discount' => 0,
                'tgl_terima' => Carbon::parse($firstDue)->subMonthNoOverflow()->toDateString(),
                'jenis_pembelian' => $plan['kind'] === 'cash' ? 'Cash Keras' : 'Kredit',
                'dp_kredit' => 20000000, 'inhouse_tenor' => 10, 'inhouse_perbulan' => 10000000, 'inhouse_jatuh_tempo' => $firstDue]);
            $this->postJson(route('customer.store'), $input)->assertOk()->assertJson(['success' => true]);
            $customer = DB::table('customer')->orderByDesc('id')->value('id');
            if ($plan['kind'] === 'paid') {$this->pay($customer, 100000000); $paidIds[] = $customer;}
            if ($plan['kind'] === 'late') {
                $payment = $plan['variant'] === 'partial' ? 5000000 : ($plan['variant'] === 'installment' ? 10000000 : 0);
                if ($payment) $this->pay($customer, $payment);
                $due = $plan['months'] * 10000000 - $payment;
                if ($due > 0) $expected[$customer] = $due;
            }
        }
        $this->assertDatabaseCount('customer', 30); $this->assertDatabaseCount('transaksi_kavling', 30); $this->assertDatabaseCount('piutang', 30);
        $this->assertSame(30, DB::table('customer')->distinct()->count('kode_customer'));
        $rows = (new JatuhTempoService)->rows()->keyBy('id');
        $this->assertCount(17, $rows);
        foreach ($expected as $customer => $amount) $this->assertEquals($amount, $rows[$customer]['due_amount'], 'Incorrect due amount for buyer '.$customer);
        foreach ($paidIds as $id) {$this->assertFalse($rows->has($id)); $this->assertDatabaseHas('piutang', ['id_customer' => $id, 'sisa_bayar' => 0]);}
        $this->assertEquals(540000000, $rows->sum('due_amount'));
        $response = $this->getJson(route('pelanggan-jatuh-tempo.notification'))->assertOk()->assertJsonPath('count', 17)->assertJsonCount(10, 'items');
        $this->assertSame($rows->values()->take(10)->pluck('id')->all(), collect($response->json('items'))->pluck('id')->all());
    }

    public function test_negative_payment_cannot_increase_receivable(): void
    {
        $this->postJson(route('customer.store'), $this->purchase())->assertOk();
        $id = DB::table('piutang')->value('id');
        $this->postJson(route('pemasukan.store'), ['tanggal' => '2026-10-14', 'nominal' => '-1000', 'id_kategori_transaksi' => 6, 'id_piutang' => $id])
            ->assertUnprocessable()->assertJsonValidationErrors('nominal');
        $this->assertDatabaseHas('piutang', ['id' => $id, 'terbayar' => 1000000]);
    }
    public function test_rounded_installment_does_not_count_down_payment_as_installment(): void
    {
        $this->postJson(route('customer.store'), $this->purchase(['id_kavling' => [1], 'jenis_pembelian' => 'Kredit', 'discount' => 1,
            'dp_kredit' => 20000000, 'inhouse_tenor' => 6, 'inhouse_perbulan' => 13333334, 'inhouse_jatuh_tempo' => '2026-10-14']))->assertOk();
        $row = (new JatuhTempoService)->rows()->first();
        $this->assertEquals(13333334, $row['due_amount']);
    }

    public function test_payment_edit_move_delete_and_overpayment_keep_balances_consistent(): void
    {
        $this->postJson(route('customer.store'), $this->purchase(['id_kavling' => [1], 'discount' => 0]))->assertOk();
        $first = DB::table('customer')->orderByDesc('id')->value('id');
        $this->postJson(route('customer.store'), $this->purchase(['id_kavling' => [2], 'discount' => 0]))->assertOk();
        $second = DB::table('customer')->orderByDesc('id')->value('id');
        $this->pay($first, 5000000);
        $payment = DB::table('pemasukan')->orderByDesc('id')->value('id');
        $firstPiutang = DB::table('piutang')->where('id_customer', $first)->value('id');
        $secondPiutang = DB::table('piutang')->where('id_customer', $second)->value('id');
        $input = ['tanggal' => '2026-10-14', 'nominal' => 7000000, 'id_piutang' => $secondPiutang];
        $this->putJson(route('pemasukan.update', $payment), $input)->assertOk();
        $this->assertDatabaseHas('piutang', ['id' => $firstPiutang, 'terbayar' => 1000000, 'sisa_bayar' => 99000000]);
        $this->assertDatabaseHas('piutang', ['id' => $secondPiutang, 'terbayar' => 8000000, 'sisa_bayar' => 192000000]);
        $this->assertDatabaseHas('pemasukan', ['id' => $payment, 'id_customer' => $second]);
        $input['nominal'] = 300000000;
        $this->putJson(route('pemasukan.update', $payment), $input)->assertUnprocessable();
        $this->assertDatabaseHas('pemasukan', ['id' => $payment, 'nominal' => 7000000]);
        $this->deleteJson(route('pemasukan.destroy', $payment))->assertOk();
        $this->assertDatabaseHas('piutang', ['id' => $secondPiutang, 'terbayar' => 1000000, 'sisa_bayar' => 199000000]);
    }

    public function test_fractional_tenor_is_rejected_instead_of_becoming_fifteen_months(): void
    {
        $this->postJson(route('customer.store'), $this->purchase(['jenis_pembelian' => 'Kredit', 'dp_kredit' => 10000000,
            'inhouse_tenor' => '1.5', 'inhouse_perbulan' => 1000000, 'inhouse_jatuh_tempo' => '2026-11-14']))
            ->assertUnprocessable()->assertJsonValidationErrors('inhouse_tenor');
        $this->assertDatabaseCount('customer', 0);
    }

}
