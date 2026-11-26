<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';

    protected $fillable = [
        'kode_customer',
        'nama_lengkap',
        'no_ktp',
        'no_ktp_p',
        'tempat_lahir',
        'tgl_lahir',
        'no_telp',
        'jenis_kelamin',
        'email',
        'npwp',
        'alamat',
        'alamat_domisili',
        'pekerjaan',
        'id_lokasi',
        'tahap_pembayaran',
        'no_wa',
        'discount',
        'id_marketing',
        'ket_cashback',
        'skema_pembayaran',
        'nominal_cicilan',
        'lama_cicilan',
        'tgl_jatuh_tempo',
        'nominal_cicilan_ct',
        'lama_cicilan_ct',
        'tgl_jatuh_tempo_ct',
        'nominal_cicilan_kr',
        'lama_cicilan_kr',
        'tgl_jatuh_tempo_kr',
        'no_kk',
        'atas_nama',
        'no_surat',
        'keterangan_legalitas',
        'IPH',
        'SHGB',
        'PAJAK',
        'catatan_legalitas',
        'percakapan_wa',
        'stt_arsip',
        'tgl_cancel',
        'alasan_cancel',
    ];

    public function marketing()
    {
        return $this->belongsTo(Marketing::class, 'id_marketing');
    }

    public function lokasi()
    {
        return $this->belongsTo(LokasiKavling::class, 'id_lokasi');
    }

    public function transaksiKavling()
    {
        return $this->hasMany(TransaksiKavling::class, 'id_customer');
    }

    public function kavling()
    {
        return $this->belongsToMany(
            KavlingPeta::class,
            'transaksi_kavling',
            'id_customer',
            'id_kavling'
        )->withPivot('tgl_terima', 'hrg_rumah');
    }

    public function lokasiKavling()
    {
        return $this->belongsTo(LokasiKavling::class, 'id_lokasi');
    }

    public function piutangs()
    {
        return $this->hasMany(Piutang::class, 'id_customer');
    }

    public function piutang()
    {
        return $this->hasOne(Piutang::class, 'id_customer');
    }

    public function pemasukans()
    {
        return $this->hasMany(Pemasukan::class, 'id_customer');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'id_bank');
    }

    public function getNominalCicilanAttribute($value)
    {
        if ($this->skema_pembayaran === 'Cash Tempo') {
            return $this->nominal_cicilan_ct ?? $value;
        }
        if ($this->skema_pembayaran === 'Kredit') {
            return $this->nominal_cicilan_kr ?? $value;
        }
        return $value;
    }

    public function getLamaCicilanAttribute($value)
    {
        if ($this->skema_pembayaran === 'Cash Tempo') {
            return $this->lama_cicilan_ct ?? $value;
        }
        if ($this->skema_pembayaran === 'Kredit') {
            return $this->lama_cicilan_kr ?? $value;
        }
        return $value;
    }

    public function getTglJatuhTempoAttribute($value)
    {
        if ($this->skema_pembayaran === 'Cash Tempo') {
            return $this->tgl_jatuh_tempo_ct ?? $value;
        }
        if ($this->skema_pembayaran === 'Kredit') {
            return $this->tgl_jatuh_tempo_kr ?? $value;
        }
        return $value;
    }

    public $timestamps = false;
}
