<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KavlingPeta extends Model
{
    protected $table = 'kavling_peta';
    protected $fillable = [
        'id_lokasi',
        'id_cluster',
        'kode_kavling',
        'panjang',
        'lebar',
        'panjang_kanan',
        'panjang_kiri',
        'lebar_depan',
        'lebar_belakang',
        'luas_tanah',
        'tipe_bangunan',
        'daya_listrik',
        'luas_bangunan',
        'hrg_meter',
        'hrg_jual',
        'id_rumah_sikumbang',
        'no_sertifikat',
        'jenis_map',
        'map',
        'matrik',
        'status',
        'keterangan',
        'atas_nama_surat',
        'id_customer',
        'tgl_jatuh_tempo',
        'stt_cicilan'
    ];

    public function lokasi()
    {
        return $this->belongsTo(LokasiKavling::class, 'id_lokasi');
    }

    // Tetap SINGULAR (customer) sesuai request
    public function customer()
    {
        return $this->belongsToMany(
            Customer::class,
            'transaksi_kavling',
            'id_kavling',
            'id_customer'
        )->withPivot('tgl_terima', 'hrg_rumah');
    }

    
    public function progres()
    {
        return $this->belongsTo(ListPenjualan::class, 'id_status_progres');
    }

    public $timestamps = false;
}