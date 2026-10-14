<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonfigurasiWa extends Model
{
    protected $table = 'konfigurasi_wa';
    protected $fillable = [
        'url_api',
        'api_key',
        'number_key',
        'stt_keteangan',
    ];
    public $timestamps = false;
}
