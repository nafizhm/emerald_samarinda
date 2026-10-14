<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class GetController extends Controller
{
    // Pastikan route 'get.customer' mengarah ke method ini
    public function getCustomer($id)
    {
        try {
            $customer = Customer::with(['kavling.lokasi'])->findOrFail($id);

            $kodeKavling = $customer->kavling->pluck('kode_kavling')->implode(', ');

            $namaLokasi = $customer->kavling->map(function ($k) {
                return $k->lokasi->nama_kavling ?? '-';
            })->unique()->implode(', ');

            return response()->json([
                'success' => true,
                'data' => [
                    'customer' => $customer,
                    'kode_kavling_gabungan' => $kodeKavling,
                    'nama_lokasi_gabungan'  => $namaLokasi
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
