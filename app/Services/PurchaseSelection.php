<?php

namespace App\Services;

use App\Models\KavlingPeta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class PurchaseSelection
{
    /** Called inside the purchase transaction so competing purchases lock the same units. */
    public function availableUnits(array $ids, int $locationId, int $discount, int $initialPayment): Collection
    {
        $units = KavlingPeta::with('lokasi')->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
        if ($units->count() !== count(array_unique($ids)) || $units->contains(fn ($unit) => (int) $unit->id_lokasi !== $locationId)) {
            throw ValidationException::withMessages(['id_kavling' => 'Pilih kavling yang berada di lokasi transaksi.']);
        }
        if ($units->contains(fn ($unit) => $unit->customer()->where('stt_arsip', 0)->exists())) {
            throw ValidationException::withMessages(['id_kavling' => 'Salah satu kavling sudah dibeli pelanggan lain. Pilih kembali kavling yang tersedia.']);
        }
        $total = (int) $units->sum('hrg_jual');
        if ($discount < 0 || $discount > $total) {
            throw ValidationException::withMessages(['discount' => 'Diskon tidak boleh melebihi harga total kavling.']);
        }
        if ($initialPayment < 0 || $initialPayment > $total - $discount) {
            throw ValidationException::withMessages(['pembayaran_booking' => 'Pembayaran awal tidak boleh melebihi harga setelah diskon.', 'dp_kredit' => 'DP tidak boleh melebihi harga setelah diskon.']);
        }
        return $units;
    }
}
