<?php
namespace Tests\Feature;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerEditTest extends CustomerPurchaseTest
{
    private function existing(): array
    {
        $this->postJson(route('customer.store'),$this->purchase(['id_kavling'=>[1],'jenis_pembelian'=>'Cash Keras','discount'=>0]))->assertOk();
        $customer=Customer::first();
        return [$customer->id, ['nama_lengkap'=>$customer->nama_lengkap,'no_ktp'=>$customer->no_ktp,'tempat_lahir'=>$customer->tempat_lahir,
            'tgl_lahir'=>$customer->tgl_lahir,'no_telp'=>$customer->no_telp,'alamat'=>$customer->alamat,'id_status_progres'=>$customer->id_status_progres,'email'=>'']];
    }
    public function test_empty_email_is_saved_as_empty_string_and_leading_zero_ids_are_preserved(): void
    {
        [$id,$values]=$this->existing();$values['email']=null;
        $this->putJson(route('customer.update',$id),$values)->assertOk()->assertJsonPath('success',true);
        $this->assertDatabaseHas('customer',['id'=>$id,'email'=>'','no_ktp'=>'0012345678901234','no_telp'=>'081234567890']);
    }
    public function test_invalid_email_and_status_do_not_change_customer(): void
    {
        [$id,$values]=$this->existing();
        $this->putJson(route('customer.update',$id),array_replace($values,['email'=>'invalid','nama_lengkap'=>'Unexpected change']))
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->putJson(route('customer.update',$id),array_replace($values,['id_status_progres'=>999]))->assertUnprocessable()->assertJsonValidationErrors('id_status_progres');
        $this->assertDatabaseHas('customer',['id'=>$id,'nama_lengkap'=>'Pembeli Uji']);
    }
    public function test_profile_update_does_not_modify_contract_or_payment_records(): void
    {
        [$id,$values]=$this->existing();
        $before=DB::table('piutang')->where('id_customer',$id)->first();
        $this->putJson(route('customer.update',$id),$values+['hrg_jual'=>1,'discount'=>99999999,'dp_kredit'=>0,'jenis_pembelian'=>'Kredit','id_bank'=>999])
            ->assertOk()->assertJsonPath('success',true);
        $this->assertDatabaseHas('customer',['id'=>$id,'jenis_pembelian'=>'Cash Keras','discount'=>0,'id_bank'=>1]);
        $this->assertEquals($before,DB::table('piutang')->where('id_customer',$id)->first());
        $this->assertDatabaseCount('pemasukan',1);
    }
    public function test_unknown_customer_returns_not_found(): void
    {
        $this->get(route('customer.edit',999))->assertNotFound();
        $this->putJson(route('customer.update',999),[])->assertNotFound();
    }
}
