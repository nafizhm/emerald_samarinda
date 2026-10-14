<?php
namespace Tests\Feature;
use App\Models\Customer;
use App\Models\User;
use App\Services\InstallmentSchedule;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstallmentPaymentTest extends CustomerPurchaseTest
{
    protected function setUp(): void
    {
        parent::setUp(); Carbon::setTestNow(Carbon::parse('2026-10-14 12:00:00', 'Asia/Jakarta'));
        Schema::create('menu', function(Blueprint $t) {$t->increments('id'); $t->string('route_name');});
        Schema::create('hak_akses', function(Blueprint $t) {$t->increments('id'); $t->integer('id_user'); $t->integer('id_menu'); $t->integer('tambah'); $t->integer('edit')->default(0); $t->integer('hapus')->default(0);});
        DB::table('menu')->insert(['id'=>1, 'route_name'=>'pembayaran.index']);
        DB::table('hak_akses')->insert(['id_user'=>1,'id_menu'=>1,'tambah'=>1]);
        $user = new User; $user->id = 1; $this->actingAs($user);
    }
    protected function tearDown(): void {Carbon::setTestNow(); parent::tearDown();}
    public function test_receipt_uses_profile_template_and_returns_pdf(): void
    {
        $id = $this->credit();
        foreach ([\App\Models\KonfigurasiAplikasi::class, \App\Models\MetodeBayar::class, \App\Models\KategoriTransaksi::class] as $class) {
            $model = new $class;
            if (Schema::hasTable($model->getTable())) continue;
            Schema::create($model->getTable(), function (Blueprint $table) use ($model) {
                $table->increments('id');
                foreach ($model->getFillable() as $column) {
                    if ($column !== 'id') $table->text($column)->nullable();
                }
            });
        }
        Schema::create('konfigurasi_media', function (Blueprint $table) {
            $table->increments('id'); $table->string('jenis_data'); $table->string('nama_file');
        });
        DB::table('konfigurasi')->insert(['nama_perusahaan'=>'Profil Receipt Test','alamat'=>'Alamat profil','telp'=>'123']);
        $template = 'receipt-test-'.\Illuminate\Support\Str::uuid().'.png';
        $image = \Illuminate\Http\UploadedFile::fake()->image($template, 1280, 673);
        copy($image->getRealPath(), public_path('config_media/'.$template));
        DB::table('konfigurasi_media')->insert(['jenis_data'=>'kwitansi','nama_file'=>$template]);
        $logoName = 'logo-'.$template;
        $logoImage = \Illuminate\Http\UploadedFile::fake()->image($logoName, 100, 100);
        copy($logoImage->getRealPath(), public_path('config_media/'.$logoName));
        DB::table('konfigurasi_media')->insert(['jenis_data'=>'logo website','nama_file'=>$logoName]);
        $payment = DB::table('pemasukan')->where('id_customer',$id)->first();
        $number = 'RECEIPT-TEST-'.\Illuminate\Support\Str::uuid();
        DB::table('pemasukan')->where('id',$payment->id)->update(['no_kwitansi'=>$number]);
        $file = public_path('kwitansi/kwitansi_'.$payment->id.'_'.$number.'.pdf');
        try {
            $response = $this->get(route('pembayaran.cetak',$payment->id))->assertOk()->assertHeader('Content-Type','application/pdf');
            $this->assertStringStartsWith('%PDF', $response->getContent());
            $this->assertStringContainsString('/Subtype /Image', $response->getContent());
            $this->assertGreaterThanOrEqual(2, substr_count($response->getContent(), '/Subtype /Image'));
            $this->assertStringContainsString('Profil Receipt Test', $response->getContent());
            $this->assertFileExists($file);
        } finally {
            @unlink(public_path('config_media/'.$template));
            @unlink(public_path('config_media/'.$logoName));
            @unlink($file);
        }
    }
    private function credit(array $extra=[]): int
    {
        $this->postJson(route('customer.store'), $this->purchase(array_replace(['id_kavling'=>[1], 'discount'=>0, 'jenis_pembelian'=>'Kredit', 'dp_kredit'=>10000000, 'inhouse_tenor'=>6, 'inhouse_perbulan'=>15000000, 'inhouse_jatuh_tempo'=>'2026-08-31'], $extra)))->assertOk();
        return DB::table('customer')->value('id');
    }
    private function payment(array $extra=[]): array {return array_replace(['mode'=>'through','month'=>3,'expected_paid'=>10000000,'date'=>'2026-10-14','bank_id'=>1], $extra);}
    public function test_schedule_has_every_month_correct_month_end_and_dp(): void
    {
        $id=$this->credit(); $plan=(new InstallmentSchedule)->forCustomer(Customer::find($id));
        $this->assertCount(7,$plan['rows']); $this->assertSame('2026-09-30',$plan['rows'][2]['date']->toDateString());
        $this->assertEquals(45000000,$plan['rows'][3]['pay_through']); $this->assertSame('Lunas',$plan['rows'][0]['status']);
        $this->assertEquals(100000000,$plan['rows']->sum('amount'));
    }
    public function test_pay_through_month_ignores_client_amount_and_does_not_double_charge(): void
    {
        $id=$this->credit(); $body=$this->payment(['amount'=>1]);
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$body)->assertOk()->assertJsonPath('remaining',45000000);
        $this->assertDatabaseHas('piutang',['id_customer'=>$id,'terbayar'=>55000000,'sisa_bayar'=>45000000]);
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$body)->assertConflict();
        $this->assertDatabaseCount('pemasukan',2);
    }
    public function test_partial_payment_is_subtracted_from_next_selected_month(): void
    {
        $id=$this->credit();
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment(['mode'=>'manual','amount'=>5000000,'month'=>null]))->assertOk();
        $plan=(new InstallmentSchedule)->forCustomer(Customer::find($id));
        $this->assertEquals(10000000,$plan['rows'][1]['balance']);
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment(['month'=>1,'expected_paid'=>15000000]))->assertOk()->assertJsonPath('remaining',75000000);
    }
    public function test_last_month_settles_exact_contract_after_rounding(): void
    {
        $id=$this->credit(['discount'=>1,'dp_kredit'=>20000000,'inhouse_perbulan'=>13333334]);
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment(['month'=>6,'expected_paid'=>20000000]))->assertOk()->assertJsonPath('remaining',0);
        $this->assertDatabaseHas('piutang',['id_customer'=>$id,'terbayar'=>99999999,'status'=>2]);
        $plan=(new InstallmentSchedule)->forCustomer(Customer::find($id)); $this->assertSame(0,$plan['rows']->sum('balance'));
    }
    public function test_invalid_selection_overpayment_archive_and_permission_are_rejected(): void
    {
        $id=$this->credit();
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment(['month'=>99]))->assertUnprocessable();
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment(['mode'=>'manual','amount'=>200000000]))->assertUnprocessable();
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment(['date'=>'2026-10-15']))->assertUnprocessable();
        DB::table('customer')->where('id',$id)->update(['stt_arsip'=>1]);
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment())->assertUnprocessable();
        DB::table('hak_akses')->update(['tambah'=>0]);
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment())->assertForbidden();
        $this->assertDatabaseCount('pemasukan',1);
    }
    public function test_cancelling_a_payment_restores_schedule_and_rejects_other_customer_payment(): void
    {
        $id=$this->credit(); DB::table('hak_akses')->update(['hapus'=>1]);
        $response=$this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment())->assertOk();
        $payment=$response->json('payment_id');
        $this->deleteJson(route('pembayaran.batalkan-cicilan',[$id,$payment]),['expected_paid'=>10000000])->assertConflict();
        $this->deleteJson(route('pembayaran.batalkan-cicilan',[$id,$payment]),['expected_paid'=>55000000])->assertOk();
        $this->assertDatabaseHas('piutang',['id_customer'=>$id,'terbayar'=>10000000,'sisa_bayar'=>90000000]);
        $this->assertDatabaseMissing('pemasukan',['id'=>$payment]);
        $this->postJson(route('customer.store'),$this->purchase(['id_kavling'=>[2],'discount'=>0]))->assertOk();
        $other=DB::table('pemasukan')->orderByDesc('id')->value('id');
        $this->deleteJson(route('pembayaran.batalkan-cicilan',[$id,$other]),['expected_paid'=>10000000])->assertNotFound();
        DB::table('hak_akses')->update(['hapus'=>0]);
        $this->deleteJson(route('pembayaran.batalkan-cicilan',[$id,$other]),['expected_paid'=>10000000])->assertForbidden();
    }
    public function test_cash_and_booking_do_not_show_fake_monthly_installments(): void
    {
        $this->postJson(route('customer.store'),$this->purchase(['id_kavling'=>[1],'discount'=>0,'jenis_pembelian'=>'Cash Keras']))->assertOk();
        $cash=(new InstallmentSchedule)->forCustomer(Customer::first());
        $this->assertCount(1,$cash['rows']); $this->assertEquals(0,$cash['remaining']);
        $this->postJson(route('customer.store'),$this->purchase(['id_kavling'=>[2],'discount'=>0]))->assertOk();
        $booking=(new InstallmentSchedule)->forCustomer(Customer::orderByDesc('id')->first());
        $this->assertCount(2,$booking['rows']); $this->assertEquals(200000000,$booking['rows']->sum('amount'));
    }

    public function test_editor_can_record_payment_and_invalid_proof_is_rejected(): void
    {
        $id=$this->credit(); DB::table('hak_akses')->update(['tambah'=>0,'edit'=>1]);
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment()+['proof'=>\Illuminate\Http\UploadedFile::fake()->create('malware.exe',10)])
            ->assertUnprocessable()->assertJsonValidationErrors('proof');
        $this->assertDatabaseCount('pemasukan',1);
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment())->assertOk();
    }

    public function test_last_installment_and_notification_agree_when_monthly_plan_is_short(): void
    {
        $id=$this->credit(['inhouse_perbulan'=>10000000,'inhouse_jatuh_tempo'=>'2026-05-14']);
        $plan=(new InstallmentSchedule)->forCustomer(Customer::find($id));
        $this->assertEquals(40000000,$plan['rows']->last()['amount']);
        $due=(new \App\Services\JatuhTempoService)->rows()->firstWhere('id',$id);
        $this->assertEquals($plan['due'],$due['due_amount']);
        $this->postJson(route('pembayaran.bayar-cicilan',$id),$this->payment(['month'=>6]))->assertOk()->assertJsonPath('remaining',0);
    }

}
