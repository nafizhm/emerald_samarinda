<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListPenjualan extends Model
{
    protected $table = 'progres_list_penjualan';
    protected $fillable = [
        'status_progres',
        'keterangan',
        'urutan',
        'warna',
        'short_name',
        'warna_bootstrap',
        'stt_tampil',
    ];
    public $timestamps = false;
}
