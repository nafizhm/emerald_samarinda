<?php
namespace Tests\Feature;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentListTest extends CustomerPurchaseTest
{
    protected function setUp(): void
    {
        parent::setUp(); Carbon::setTestNow(Carbon::parse('2026-10-14 12:00:00','Asia/Jakarta'));
        Schema::create('menu',function(Blueprint $t) {$t->increments('id'); $t->string('route_name');});
        Schema::create('hak_akses',function(Blueprint $t) {$t->increments('id'); $t->integer('id_user'); $t->integer('id_menu');});
    }
    private function seedPayments(): void
    {
        $this->postJson(route('customer.store'),$this->purchase(['id_kavling'=>[1],'discount'=>0,'jenis_pembelian'=>'Cash Keras','nama_lengkap'=>'<script>contoh</script>']))->assertOk();
        $this->postJson(route('customer.store'),$this->purchase(['id_kavling'=>[2],'discount'=>0,'jenis_pembelian'=>'Kredit','nama_lengkap'=>'Pembeli Kredit','dp_kredit'=>20000000,'inhouse_tenor'=>18,'inhouse_perbulan'=>10000000,'inhouse_jatuh_tempo'=>'2026-09-15']))->assertOk();
        Customer::create(['nama_lengkap'=>'Tanpa kontrak','kode_customer'=>'UNSET','id_lokasi'=>1,'email'=>'']);
    }
    protected function tearDown(): void {Carbon::setTestNow();parent::tearDown();}
    private function listing(array $extra=[])
    {
        $this->app->forgetInstance('datatables.request');
        $params=array_replace(['draw'=>1,'start'=>0,'length'=>10,'columns'=>[
            ['data'=>'customer','name'=>'customer','searchable'=>'true','orderable'=>'false'],
            ['data'=>'unit','name'=>'unit','searchable'=>'true','orderable'=>'false']
        ]],$extra);
        return $this->getJson(route('pembayaran.index').'?'.http_build_query($params),['X-Requested-With'=>'XMLHttpRequest']);
    }
    public function test_summary_and_listing_show_balances_without_transaction_details(): void
    {
        $this->seedPayments();
        $response=$this->listing()->assertOk()->assertJsonPath('summary.total',3)->assertJsonPath('summary.settled',1)
            ->assertJsonPath('summary.outstanding',1)->assertJsonPath('summary.due',1)->assertJsonPath('summary.balance',180000000)->assertJsonCount(3,'data');
        $first=$response->json('data.0');
        $this->assertStringContainsString('Pembeli Kredit',$first['customer']);
        $this->assertArrayNotHasKey('rincian_tagihan',$first); $this->assertArrayNotHasKey('pemasukans',$first); $this->assertArrayNotHasKey('no_ktp',$first);
        $this->assertStringContainsString('Terlambat',$first['payment_status']);
        $this->assertStringContainsString('Buka pembayaran',$first['action']);
        $json=$response->getContent();$this->assertStringNotContainsString('<script>contoh</script>',$json);
    }
    public function test_paid_unpaid_due_project_and_method_filters_are_consistent(): void
    {
        $this->seedPayments();
        $this->listing(['status'=>'Lunas'])->assertOk()->assertJsonCount(1,'data')->assertJsonPath('recordsFiltered',1);
        $this->listing(['status'=>'Terhutang'])->assertOk()->assertJsonCount(1,'data');
        $this->listing(['status'=>'JatuhTempo'])->assertOk()->assertJsonCount(1,'data');
        $this->listing(['lokasi'=>2])->assertOk()->assertJsonCount(0,'data')->assertJsonPath('summary.total',3);
        $this->listing(['jenis'=>'Cash Keras'])->assertOk()->assertJsonCount(1,'data');
    }
    public function test_search_matches_unit_and_customer_code_and_does_not_mark_missing_contract_as_paid(): void
    {
        $this->seedPayments();
        $this->listing(['search'=>['value'=>'A2','regex'=>'false']])->assertOk()->assertJsonCount(1,'data');
        $response=$this->listing(['search'=>['value'=>'UNSET','regex'=>'false']])->assertOk()->assertJsonCount(1,'data');
        $this->assertStringContainsString('Belum ada kontrak',$response->json('data.0.payment_status'));
        DB::table('customer')->where('nama_lengkap','Pembeli Kredit')->update(['stt_arsip'=>1]);
        $this->listing()->assertOk()->assertJsonPath('summary.total',2)->assertJsonPath('summary.outstanding',0);
    }
}
