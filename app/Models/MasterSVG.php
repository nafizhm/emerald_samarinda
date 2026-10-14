<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSVG extends Model
{
    protected $table = 'master_svg';
    public $timestamps = false;
    protected $fillable = [
        'id_lokasi',
        'header_xml',
        'header_svg',
        'polygon_svg',
        'path_svg',
        'footer_svg',
        'lebar',
        'tinggi',
        'ukuran_dashboard',
    ];
}
