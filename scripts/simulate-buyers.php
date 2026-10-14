<?php
// Default: rollback. Use --persist to save the verified simulation project.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Keuangan\PemasukanController;
use App\Services\JatuhTempoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function requiredDefaults(string $table): array {
    $values = [];
    foreach (Schema::getColumns($table) as $column) {
        if ($column['nullable'] || $column['default'] !== null || $column['auto_increment']) continue;
        $values[$column['name']] = preg_match('/int|decimal|double|float/', $column['type_name']) ? 0 : '';
    }
    return $values;
}
function verify(bool $condition, string $message): void {if (!$condition) throw new RuntimeException($message);}
$today = Carbon::today('Asia/Jakarta');
$bank = DB::table('bank')->value('id');
$status = DB::table('progres_list_penjualan')->value('id');
verify((bool) $bank && (bool) $status, 'Rekening dan status penjualan harus tersedia.');
$persist = in_array('--persist', $argv, true);
if ($persist && DB::table('lokasi_kavling')->where('nama_kavling', 'SIMULASI 30 PEMBELI')->exists()) {
    echo 'Proyek SIMULASI 30 PEMBELI sudah tersedia; tidak menambahkan data duplikat.'.PHP_EOL;
    exit(0);
}
$level = DB::transactionLevel();
DB::beginTransaction();
try {
    $project = DB::table('lokasi_kavling')->insertGetId(array_replace(requiredDefaults('lokasi_kavling'), ['nama_kavling' => 'SIMULASI 30 PEMBELI', 'nama_singkat' => 'SIMTEST', 'stt_tampil' => 1]));
    DB::table('master_svg')->insert(array_replace(requiredDefaults('master_svg'), [
        'id_lokasi' => $project,
        'header_xml' => '<?xml version="1.0" encoding="UTF-8"?>',
        'header_svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="[[lebar]]" height="[[tinggi]]" viewBox="0 0 960 640"><rect width="960" height="640" fill="#f8fafc"/><text x="40" y="40" font-size="22" fill="#24334b">SIMULASI / 30 KAVLING</text>',
        'polygon_svg' => '<g transform="[[3]]"><polygon points="[[1]]" fill="[[2]]" stroke="#64748b" stroke-width="2"/><text x="56" y="38" text-anchor="middle" font-size="16" fill="#24334b">[[4]]</text></g>',
        'path_svg' => '<path d="[[1]]" fill="[[2]]" transform="[[3]]"/>',
        'footer_svg' => '</svg>',
    ]));
    $plans = array_merge(array_fill(0, 6, ['kind' => 'cash']), array_fill(0, 3, ['kind' => 'current']), array_fill(0, 3, ['kind' => 'paid']));
    for ($months = 1; $months <= 6; $months++) foreach (['unpaid','partial','installment'] as $variant) $plans[] = ['kind' => 'late', 'months' => $months, 'variant' => $variant];
    $ids = []; $expected = []; $report = [];
    foreach ($plans as $index => $plan) {
        $unit = DB::table('kavling_peta')->insertGetId(array_replace(requiredDefaults('kavling_peta'), ['id_lokasi' => $project, 'kode_kavling' => 'SIM-'.($index+1), 'panjang' => 12, 'lebar' => 10, 'luas_tanah' => 120, 'hrg_jual' => 120000000, 'jenis_map' => 'polygon', 'map' => '0,0 112,0 112,64 0,64', 'matrik' => 'matrix(1 0 0 1 '.(40+($index%6)*144).' '.(80+intdiv($index,6)*104).')', 'keterangan' => 'Data contoh proyek SIMULASI']));
        $anchor = $today->copy()->startOfMonth()->addDays(14);
        $date = $plan['kind'] === 'late' ? $anchor->copy()->subMonthsNoOverflow($plan['months']) : ($plan['kind'] === 'paid' ? $today->copy()->subMonthsNoOverflow(6) : $today->copy()->addMonthNoOverflow());
        $input = ['tgl_terima' => $date->copy()->subMonthNoOverflow()->toDateString(), 'nama_lengkap' => 'SIMULASI '.str_pad($index+1, 2, '0', STR_PAD_LEFT),
            'no_ktp' => '990000000000'.str_pad($index+1, 4, '0', STR_PAD_LEFT), 'tempat_lahir' => 'Balikpapan', 'tgl_lahir' => '1990-01-01',
            'no_telp' => '080000000000', 'alamat' => 'Data simulasi otomatis', 'id_lokasi' => $project, 'id_kavling' => [$unit],
            'id_status_progres' => $status, 'id_bank' => $bank, 'hrg_jual' => 120000000, 'discount' => 0,
            'jenis_pembelian' => $plan['kind'] === 'cash' ? 'Cash Keras' : 'Kredit', 'dp_kredit' => 20000000,
            'inhouse_tenor' => 10, 'inhouse_perbulan' => 10000000, 'inhouse_jatuh_tempo' => $date->toDateString()];
        $response = app(CustomerController::class)->store(Request::create('/simulation', 'POST', $input));
        verify($response->getStatusCode() === 200 && $response->getData()->success, 'Input pembeli '.($index+1).' gagal: '.$response->getContent());
        $customer = DB::table('customer')->where('id_lokasi', $project)->orderByDesc('id')->value('id'); $ids[] = $customer;
        $payment = $plan['kind'] === 'paid' ? 100000000 : ($plan['kind'] === 'late' ? ($plan['variant'] === 'partial' ? 5000000 : ($plan['variant'] === 'installment' ? 10000000 : 0)) : 0);
        if ($payment) {
            $piutang = DB::table('piutang')->where('id_customer', $customer)->value('id');
            $response = app(PemasukanController::class)->store(Request::create('/simulation-payment', 'POST', ['tanggal' => $today->toDateString(), 'nominal' => $payment, 'id_kategori_transaksi' => 6, 'id_piutang' => $piutang, 'keterangan' => 'Pembayaran simulasi otomatis']));
            verify($response->getStatusCode() === 200, 'Pembayaran simulasi gagal.');
        }
        if ($plan['kind'] === 'late') {
            $monthsDue = $plan['months'] + ($today->day >= 15 ? 1 : 0);
            $due = $monthsDue * 10000000 - $payment;
            if ($due > 0) $expected[$customer] = $due;
        }
        $balance = DB::table('piutang')->where('id_customer', $customer)->first();
        verify((float) $balance->nominal === (float) $balance->terbayar + (float) $balance->sisa_bayar, 'Saldo piutang tidak seimbang.');
        if (in_array($plan['kind'], ['paid','cash'])) verify((float) $balance->sisa_bayar === 0.0 && (int) $balance->status === 2, 'Status pelunasan tidak sesuai.');
        $report[] = ['buyer' => $index+1, 'scenario' => $plan, 'remaining' => (float) $balance->sisa_bayar];
    }
    $rows = (new JatuhTempoService)->rows()->whereIn('id', $ids)->keyBy('id');
    verify($rows->count() === count($expected), 'Jumlah pelanggan jatuh tempo tidak sesuai.');
    foreach ($expected as $id => $amount) verify((float) $rows[$id]['due_amount'] === (float) $amount, 'Nominal jatuh tempo tidak sesuai.');
    $summary = ['date' => $today->toDateString(), 'database' => config('database.default'), 'buyers' => count($ids), 'cash' => 6, 'current_credit' => 3, 'paid_credit' => 3, 'late_scenarios' => 18, 'due_customers' => $rows->count(), 'due_amount' => $rows->sum('due_amount'), 'project_id' => $project, 'customer_ids' => $ids, 'persisted' => $persist, 'result' => 'PASS', 'scenarios' => $report];
    if (!is_dir(__DIR__.'/../docs/testing')) mkdir(__DIR__.'/../docs/testing', 0777, true);
    file_put_contents(__DIR__.'/../docs/testing/buyer-simulation.json', json_encode($summary, JSON_PRETTY_PRINT));
    if ($persist) DB::commit();
    echo json_encode(array_diff_key($summary, ['scenarios' => true]), JSON_PRETTY_PRINT).PHP_EOL;
} finally {while (DB::transactionLevel() > $level) DB::rollBack();}
