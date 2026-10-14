<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemasukan extends Model
{
    protected $table    = 'pemasukan';
    protected $fillable = ['id', 'id_hutang', 'id_piutang', 'id_customer', 'id_lokasi', 'id_mutasi', 'id_bank', 'tanggal', 'nominal', 'lampiran', 'no_kwitansi', 'id_kategori_transaksi', 'keterangan'];
    public $timestamps  = false;

    public function metode()
    {
        return $this->belongsTo(MetodeBayar::class, 'id_bank');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriTransaksi::class, 'id_kategori_transaksi');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }
}
