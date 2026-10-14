<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonfigurasiMedia extends Model
{
    protected $table = 'konfigurasi_media';
    protected $fillable = [
        'jenis_data',
        'nama_file',
    ];
    public $timestamps = false;
}
