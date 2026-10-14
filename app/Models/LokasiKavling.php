<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LokasiKavling extends Model
{
    protected $table = 'lokasi_kavling';
    public $timestamps = false;
    protected $fillable = [
        'nama_kavling',
        'foto_kavling',
        'nama_singkat',
        'header',
        'alamat',
        'urutan',
        'nama_perusahaan',
        'nama_admin',
        'nama_mengetahui',
        'alamat_perusahaan',
        'telp_perusahaan',
        'bg_kwitansi',
        'kop_surat',
        'kota_penandatangan',
        'nama_penandatangan',
        'jabatan_penandatangan',
        'stt_tampil',
    ];

    public function kavlingPeta()
    {
        return $this->hasMany(KavlingPeta::class, 'id_lokasi');
    }
    public function masterSvg()
    {
        return $this->hasOne(MasterSVG::class, 'id_lokasi');
    }
}
