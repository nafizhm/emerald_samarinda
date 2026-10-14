<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';

    protected $fillable = [
        'kode_customer',
        'nama_lengkap',
        'no_ktp',
        'no_ktp_p',
        'tempat_lahir',
        'tgl_lahir',
        'no_telp',
        'jenis_kelamin',
        'email',
        'npwp',
        'alamat',
        'alamat_domisili',
        'pekerjaan',
        'id_lokasi',
        'id_status_progres',
        'no_wa',
        'discount',
        'id_marketing',
        'ket_cashback',
        'jenis_pembelian',
        'inhouse_perbulan',
        'inhouse_tenor',
        'inhouse_jatuh_tempo',
        'pembayaran_booking',
        'tgl_batas_booking',
        'dp_kredit',
        'id_bank',
        'no_kk',
        'stt_arsip',
        'tgl_cancel',
        'alasan_cancel',
    ];

    public function marketing()
    {
        return $this->belongsTo(Marketing::class, 'id_marketing');
    }

    public function lokasi()
    {
        return $this->belongsTo(LokasiKavling::class, 'id_lokasi');
    }

    public function transaksiKavling()
    {
        return $this->hasMany(TransaksiKavling::class, 'id_customer');
    }

    public function kavling()
    {
        return $this->belongsToMany(
            KavlingPeta::class,
            'transaksi_kavling',
            'id_customer',
            'id_kavling'
        )->withPivot('tgl_terima', 'hrg_rumah');
    }

    public function progres()
    {
        return $this->belongsTo(ListPenjualan::class, 'id_status_progres');
    }

    public function lokasiKavling()
    {
        return $this->belongsTo(LokasiKavling::class, 'id_lokasi');
    }

    public function piutangs()
    {
        return $this->hasMany(Piutang::class, 'id_customer');
    }

    public function pemasukans()
    {
        return $this->hasMany(Pemasukan::class, 'id_customer');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'id_bank');
    }

    public $timestamps = false;
}
