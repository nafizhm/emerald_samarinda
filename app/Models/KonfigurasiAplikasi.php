<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonfigurasiAplikasi extends Model
{
    protected $table = 'konfigurasi';

    protected $fillable = [
        'nama_perusahaan',
        'alamat',
        'email',
        'telp',
        'hape',
        'npwp_perusahaan',
        'front_page',
        'pesan_jatuh_tempo',
    ];

    public $timestamps = false;
}
