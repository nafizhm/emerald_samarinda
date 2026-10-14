<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKavling extends Model
{
    protected $table = 'transaksi_kavling';

    public $timestamps = false;
    protected $fillable = [
        'id_customer',
        'id_kavling',
        'tgl_terima',
        'hrg_rumah'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function kavling()
    {
        return $this->belongsTo(KavlingPeta::class, 'id_kavling');
    }
}
