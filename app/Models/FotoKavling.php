<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FotoKavling extends Model
{
    protected $table = 'foto_kavling';
    protected $fillable = [
        'tanggal',
        'id_kavling',
        'file_name',
        'lampiran',
        'keterangan',
    ];
    public $timestamps = false;

}
