<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadFile extends Model
{
    protected $table = 'file_nasabah';

    protected $fillable = [
        'tanggal',
        'id_customer',
        'folder',
        'nama_file',
        'keterangan',
        'lampiran',
    ];

    public $timestamps = false;
}
