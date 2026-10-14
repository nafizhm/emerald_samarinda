<?php

namespace App\Http\Controllers;

use App\Services\JatuhTempoService;
use Illuminate\Http\Request;

class JatuhTempoController extends Controller
{
    public function index(Request $request, JatuhTempoService $service)
    {
        $rows = $service->rows();
        $summary = ['total' => $rows->count(), 'today' => $rows->where('late_days', 0)->count(),
            'late' => $rows->where('late_days', '>', 0)->count(), 'amount' => $rows->sum('due_amount')];
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'semua');
        $filtered = $rows->filter(function ($row) use ($search, $status) {
            if ($status === 'hari-ini' && $row['late_days'] > 0) return false;
            if ($status === 'terlambat' && $row['late_days'] === 0) return false;
            return $search === '' || str_contains(mb_strtolower($row['name'].' '.$row['codes'].' '.$row['locations']), mb_strtolower($search));
        })->values();
        $page = max(1, (int) $request->query('page', 1));
        $customers = new \Illuminate\Pagination\LengthAwarePaginator($filtered->forPage($page, 20), $filtered->count(), 20, $page,
            ['path' => $request->url(), 'query' => $request->query()]);
        return view('admin.pembayaran.pelanggan_jatuh_tempo', compact('customers', 'summary', 'search', 'status'));
    }

    public function notification(JatuhTempoService $service)
    {
        $rows = $service->rows();
        $items = $rows->take(10)->map(fn ($row) => [
            'id' => $row['id'], 'name' => $row['name'], 'codes' => $row['codes'], 'locations' => $row['locations'],
            'due_date' => $row['due_date']->locale('id')->translatedFormat('d M Y'),
            'late_days' => $row['late_days'], 'due_amount' => $row['due_amount'],
            'detail_url' => route('pembayaran.detail', $row['id']),
        ])->values();
        return response()->json(['count' => $rows->count(), 'items' => $items])->header('Cache-Control', 'no-store, private');
    }
}
