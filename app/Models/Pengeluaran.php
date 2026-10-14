<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table    = 'pengeluaran';
    protected $fillable = [
        'id_hutang',
        'id_piutang',
        'id_po',
        'id_mutasi',
        'id_proyek_bangunan_detail',
        'id_proyek_jalan_detail',
        'id_proyek_saluran_detail',
        'tanggal',
        'id_bank',
        'nominal',
        'lampiran',
        'id_kategori_transaksi',
        'keterangan',
    ];
    public $timestamps = false;
}
