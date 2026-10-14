<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marketing extends Model
{
    protected $table = 'marketing';
    protected $fillable = [
        'kode_marketing',
        'nama_marketing',
        'jenis_kelamin',
        'alamat',
        'pekerjaan',
        'no_telp',
        'foto',
        'status',
        'id_level',
        'stt_marketing',
    ];
    public $timestamps = false;
}
