<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Piutang extends Model
{
    use HasFactory;

    protected $table = 'piutang';
    public $timestamps = false;

    protected $fillable = [
        'id_invoice',
        'tanggal_piutang',
        'id_bank',
        'id_transaksi_kavling',
        'deskripsi',
        'nominal',
        'lampiran',
        'status',
        'terbayar',
        'sisa_bayar',
        'tgl_pelunasan',
        'id_kategori_transaksi',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriTransaksi::class, 'id_kategori_transaksi');
    }

    public function transaksiKavling()
    {
        return $this->belongsTo(TransaksiKavling::class, 'id_transaksi_kavling');
    }

    public function customer()
    {
        return $this->hasOneThrough(
            Customer::class,
            TransaksiKavling::class,
            'id',           // FK di transaksi_kavling
            'id',           // FK di customer
            'id_transaksi_kavling', // local key di piutang
            'id_customer'   // local key di transaksi_kavling
        );
    }
}
