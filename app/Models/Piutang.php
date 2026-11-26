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
        'id_customer',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function transaksiKavling()
    {
        return $this->hasMany(TransaksiKavling::class, 'id_customer', 'id_customer');
    }

    public function pemasukans()
    {
        return $this->hasMany(Pemasukan::class, 'id_piutang');
    }
}
