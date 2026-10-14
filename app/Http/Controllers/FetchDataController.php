<?php

namespace App\Http\Controllers;

use App\Models\Marketing;
use Illuminate\Http\Request;

class FetchDataController extends Controller
{
    public function getMarketing()
    {
        try {
            $data = Marketing::all()->map(function($item) {
                return [
                    'id' => $item->id,
                    'nama_marketing' => $item->nama_marketing
                ];
            });

            if (!$data->count()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data tidak ditemukan',
                    'data'    => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data'    => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => []
            ], 500);
        }
    }
}
