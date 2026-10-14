<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersyaratanLegal extends Model
{
    protected $table = 'persyaratan_legal';

    public $timestamps = false;

    protected $fillable = [
        'id_customer',
        'IPH',
        'SHGB',
        'pajak',
        'catatan_kekurangan',
        'percakapan_wa',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }
}
