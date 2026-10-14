<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notaris extends Model
{
    protected $table = 'notaris';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'nama_notaris',
        'alamat_notaris',
        'telp_notaris',
        'keterangan_notaris',
    ];
}
