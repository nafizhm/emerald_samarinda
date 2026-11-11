<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadFile extends Model
{
    protected $table = 'dokumen_customer';

    protected $fillable = [
        'tanggal',
        'id_customer',
        'jenis_file',
        'keterangan',
        'lampiran',
    ];

    public $timestamps = false;

    public function getNamaFileAttribute()
    {
        return $this->attributes['jenis_file'] ?? null;
    }

    public function setNamaFileAttribute($value)
    {
        $this->attributes['jenis_file'] = $value;
    }
}
