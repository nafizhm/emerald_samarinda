<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Customer;
use App\Models\KategoriTransaksi;
use App\Models\KavlingPeta;
use App\Models\KonfigurasiAplikasi;
use App\Models\KonfigurasiMedia;
use App\Models\KonfigurasiWa;
use App\Models\ListPenjualan;
use App\Models\LokasiKavling;
use App\Models\MetodeBayar;
use App\Models\Pemasukan;
use App\Models\Piutang;
use App\Models\TemplatePesan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use TCPDF;
use Yajra\DataTables\Facades\DataTables;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions('pembayaran.index');
        $financials = DB::table('piutang')->select('id_customer')
            ->selectRaw('SUM(nominal) as contract, SUM(terbayar) as paid, SUM(sisa_bayar) as remaining')->groupBy('id_customer');
        $base = Customer::query()->leftJoinSub($financials, 'balances', 'customer.id', '=', 'balances.id_customer')->where('customer.stt_arsip', 0);
        $stats = (clone $base)->selectRaw('COUNT(*) as total, SUM(CASE WHEN balances.contract IS NOT NULL AND balances.remaining <= 0 THEN 1 ELSE 0 END) as settled, SUM(CASE WHEN balances.remaining > 0 THEN 1 ELSE 0 END) as outstanding, SUM(CASE WHEN balances.remaining > 0 THEN balances.remaining ELSE 0 END) as balance_total')->first();
        $dueRows = app(\App\Services\JatuhTempoService::class)->rows()->keyBy('id');
        $summary = ['total' => (int) $stats->total, 'settled' => (int) $stats->settled, 'outstanding' => (int) $stats->outstanding,
            'due' => $dueRows->count(), 'balance' => (int) $stats->balance_total];
        if ($request->ajax()) {
            $data = $base->select('customer.*', 'balances.contract', 'balances.paid', 'balances.remaining')->with(['kavling.lokasi']);
            if ($request->status === 'Lunas') $data->whereNotNull('balances.contract')->where('balances.remaining', '<=', 0);
            elseif ($request->status === 'Terhutang') $data->where('balances.remaining', '>', 0);
            elseif ($request->status === 'JatuhTempo') $data->whereIn('customer.id', $dueRows->keys());
            if ($request->filled('lokasi')) $data->where('customer.id_lokasi', $request->lokasi);
            if ($request->filled('jenis')) $data->where('customer.jenis_pembelian', $request->jenis);
            if ($dueRows->isNotEmpty()) {
                $placeholders = implode(',', array_fill(0, $dueRows->count(), '?'));
                $data->orderByRaw("CASE WHEN customer.id IN ({$placeholders}) THEN 0 ELSE 1 END", $dueRows->keys()->all());
            }
            $data->orderByRaw('CASE WHEN balances.remaining > 0 THEN 0 ELSE 1 END')->orderByDesc('customer.id');
            $currency = fn ($value) => 'Rp '.number_format(max(0, (int) $value), 0, ',', '.');
            return DataTables::of($data)
                ->addColumn('customer', function ($row) {
                    $initial = mb_strtoupper(mb_substr($row->nama_lengkap, 0, 1));
                    return '<div class="payment-person"><span class="payment-avatar" aria-hidden="true">'.e($initial).'</span><div><strong>'.e($row->nama_lengkap).'</strong><small>'.e($row->kode_customer ?: '-').'</small></div></div>';
                })
                ->filterColumn('customer', fn ($query, $keyword) => $query->where(fn ($q) => $q->where('customer.nama_lengkap', 'like', "%{$keyword}%")->orWhere('customer.kode_customer', 'like', "%{$keyword}%")->orWhere('customer.no_telp', 'like', "%{$keyword}%")))
                ->addColumn('unit', function ($row) {
                    $codes = $row->kavling->pluck('kode_kavling')->filter()->implode(', ');
                    $locations = $row->kavling->map(fn ($unit) => $unit->lokasi?->nama_kavling)->filter()->unique()->implode(', ');
                    return '<div class="payment-unit"><strong>'.e($codes ?: 'Belum ada kavling').'</strong><small>'.e($locations ?: '-').'</small></div>';
                })
                ->filterColumn('unit', fn ($query, $keyword) => $query->whereHas('kavling', fn ($q) => $q->where('kode_kavling', 'like', "%{$keyword}%")->orWhereHas('lokasi', fn ($location) => $location->where('nama_kavling', 'like', "%{$keyword}%"))))
                ->addColumn('method', fn ($row) => '<span class="payment-method">'.e($row->jenis_pembelian === 'Cash Keras' ? 'Cash' : ($row->jenis_pembelian ?: '-')).'</span>')
                ->addColumn('payment_progress', function ($row) use ($currency) {
                    if ($row->contract === null) return '<span class="payment-muted">Kontrak belum tersedia</span>';
                    $percent = $row->contract > 0 ? min(100, round(max(0, $row->paid) / $row->contract * 100)) : 100;
                    return '<div class="payment-progress"><div><strong>'.$percent.'%</strong><span>'.$currency($row->paid).'</span></div><div class="payment-progress-track" role="progressbar" aria-label="Progres pembayaran" aria-valuenow="'.$percent.'" aria-valuemin="0" aria-valuemax="100"><span style="width:'.$percent.'%"></span></div><small>Nilai kontrak '.$currency($row->contract).'</small></div>';
                })
                ->addColumn('balance', fn ($row) => '<strong class="payment-balance '.($row->remaining > 0 ? '' : 'settled').'">'.($row->contract === null ? '-' : $currency($row->remaining)).'</strong>')
                ->addColumn('payment_status', function ($row) use ($dueRows) {
                    if ($row->contract === null) return '<span class="payment-status neutral">Belum ada kontrak</span>';
                    if ($row->remaining <= 0) return '<span class="payment-status settled"><i class="bi bi-check-circle"></i>Lunas</span>';
                    if ($dueRows->has($row->id)) {
                        $due = $dueRows[$row->id];
                        return '<span class="payment-status due">'.($due['late_days'] ? 'Terlambat' : 'Jatuh tempo hari ini').'</span><small class="payment-status-note">'.($due['late_days'] ? 'Telat '.$due['late_days'].' hari' : 'Perlu dibayar hari ini').'</small>';
                    }
                    return '<span class="payment-status pending">Belum lunas</span>';
                })
                ->addColumn('action', fn ($row) => '<a class="payment-open" href="'.e(route('pembayaran.detail', $row->id)).'" aria-label="Buka pembayaran '.e($row->nama_lengkap).'"><span>Buka pembayaran</span><i class="bi bi-arrow-up-right"></i></a>')
                ->only(['customer', 'unit', 'method', 'payment_progress', 'balance', 'payment_status', 'action'])
                ->rawColumns(['customer', 'unit', 'method', 'payment_progress', 'balance', 'payment_status', 'action'])
                ->with('summary', $summary)->make(true);
        }
        $locations = LokasiKavling::orderBy('nama_kavling')->get(['id', 'nama_kavling']);
        $methods = Customer::where('stt_arsip', 0)->whereNotNull('jenis_pembelian')->distinct()->orderBy('jenis_pembelian')->pluck('jenis_pembelian');
        return view('admin.pembayaran.index', compact('permissions', 'summary', 'locations', 'methods'));
    }

    public function show($id)
    {
        $customer = Customer::with(['piutangs', 'lokasi', 'kavling'])
            ->findOrFail($id);

        if (in_array($customer->jenis_pembelian, ['Kredit', 'Cash Keras', 'Booking']) && $customer->piutangs->count() <= 1) {
            $customer->load(['pemasukans', 'kavling.lokasi']);
            $schedule = app(\App\Services\InstallmentSchedule::class)->forCustomer($customer);
            $banks = \App\Models\Bank::all();
            $permissions = HakAksesController::getUserPermissions('pembayaran.index');
            $canPay = (bool) ($permissions['tambah'] || $permissions['edit']);
            $canDelete = (bool) $permissions['hapus'];
            return view('admin.pembayaran.installments', compact('customer', 'schedule', 'banks', 'canPay', 'canDelete'));
        }

        $totalHargaJual = $customer->kavling->sum(function ($kav) {
            return $kav->pivot->hrg_rumah;
        });

        $kodeKavlingGabungan = $customer->kavling->pluck('kode_kavling')->implode(', ');

        $metodeBayar = MetodeBayar::all();

        $kategoriTransaksiPemasukan = KategoriTransaksi::whereIn('id', [25, 26, 27, 28, 102])
            ->get();

        $kategoriTransaksiTagihan = KategoriTransaksi::whereIn('id', [30])
            ->get();

        $piutang = Piutang::with('kategori')
            ->where('id_customer', $id)
            ->where('id_kategori_transaksi', '!=', 0)
            ->get();

        return view('admin.pembayaran.detail', compact(
            'customer',
            'metodeBayar',
            'kategoriTransaksiPemasukan',
            'kategoriTransaksiTagihan',
            'piutang',
            'totalHargaJual',
            'kodeKavlingGabungan'
        ));
    }

    public function payInstallments(Request $request, $id)
    {
        $permissions = HakAksesController::getUserPermissions('pembayaran.index');
        abort_unless($permissions['tambah'] || $permissions['edit'], 403);
        $input = $request->validate([
            'mode' => 'required|in:through,manual', 'month' => 'required_if:mode,through|nullable|integer|min:0|max:600',
            'amount' => 'required_if:mode,manual|nullable|integer|min:1', 'expected_paid' => 'required|integer|min:0',
            'date' => 'required|date|before_or_equal:today', 'bank_id' => 'required|integer|exists:bank,id',
            'note' => 'nullable|string|max:500', 'proof' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:2048',
        ]);
        $proofPath = null;
        try {
        return DB::transaction(function () use ($input, $id, $request, &$proofPath) {
            $customer = Customer::lockForUpdate()->findOrFail($id);
            abort_if($customer->stt_arsip, 422, 'Pelanggan telah diarsipkan.');
            $customer->setRelation('piutangs', Piutang::where('id_customer', $id)->lockForUpdate()->get());
            abort_unless($customer->piutangs->count() === 1 && in_array($customer->jenis_pembelian, ['Kredit', 'Cash Keras', 'Booking']), 422, 'Pembayaran ini memerlukan satu kontrak kavling.');
            $plan = app(\App\Services\InstallmentSchedule::class)->forCustomer($customer);
            if ($plan['paid'] !== (int) $input['expected_paid']) return response()->json(['message' => 'Pembayaran telah berubah. Muat ulang halaman sebelum melanjutkan.'], 409);
            $amount = (int) ($input['amount'] ?? 0);
            $label = 'Pembayaran kavling';
            if ($input['mode'] === 'through') {
                $row = $plan['rows']->firstWhere('month', (int) $input['month']);
                abort_unless($plan['valid'] && $row, 422, 'Bulan cicilan tidak tersedia.');
                $amount = $row['pay_through'];
                $label = $row['month'] === 0 ? $row['label'] : 'Pembayaran sampai '.$row['label'];
            }
            if ($amount <= 0 || $amount > $plan['remaining']) throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'Nominal harus lebih dari nol dan tidak melebihi sisa pembayaran.']);
            $piutang = $customer->piutangs->first();
            $number = 'KVL-'.$id.'-'.Str::upper(Str::random(12));
            $proofName = '';
            if ($request->hasFile('proof')) {
                $proofName = Str::random(32).'.'.$request->file('proof')->extension();
                $directory = public_path('assets/keuangan/pemasukan');
                \Illuminate\Support\Facades\File::ensureDirectoryExists($directory);
                $request->file('proof')->move($directory, $proofName);
                $proofPath = $directory.DIRECTORY_SEPARATOR.$proofName;
            }
            $payment = Pemasukan::create([
                'id_customer' => $id, 'id_lokasi' => $customer->id_lokasi, 'id_piutang' => $piutang->id,
                'id_bank' => $input['bank_id'], 'tanggal' => $input['date'], 'nominal' => $amount,
                'id_kategori_transaksi' => $input['mode'] === 'through' && (int) $input['month'] === 0
                    ? ($customer->jenis_pembelian === 'Kredit' ? 26 : 25)
                    : ($customer->jenis_pembelian === 'Kredit' ? 28 : 27),
                'no_kwitansi' => $number, 'keterangan' => $label.(!empty($input['note']) ? ' / '.$input['note'] : ''), 'lampiran' => $proofName,
            ]);
            $remaining = $plan['remaining'] - $amount;
            $piutang->update(['terbayar' => $plan['paid'] + $amount, 'sisa_bayar' => $remaining,
                'status' => $remaining === 0 ? 2 : 1, 'tgl_pelunasan' => $remaining === 0 ? $input['date'] : null]);
            return response()->json(['success' => true, 'message' => 'Pembayaran berhasil dicatat.', 'payment_id' => $payment->id,
                'receipt_url' => route('pembayaran.cetak', $payment->id), 'remaining' => $remaining]);
        });
        } catch (\Throwable $error) {
            if ($proofPath && is_file($proofPath)) unlink($proofPath);
            throw $error;
        }
    }

    public function cancelInstallment(Request $request, $id, $payment)
    {
        abort_unless(HakAksesController::getUserPermissions('pembayaran.index')['hapus'], 403);
        $request->validate(['expected_paid' => 'required|integer|min:0']);
        return DB::transaction(function () use ($request, $id, $payment) {
            $customer = Customer::lockForUpdate()->findOrFail($id);
            $piutangs = Piutang::where('id_customer', $id)->lockForUpdate()->get();
            if ((int) $piutangs->sum('terbayar') !== (int) $request->expected_paid) return response()->json(['message' => 'Saldo berubah. Muat ulang halaman sebelum membatalkan pembayaran.'], 409);
            Pemasukan::where('id_customer', $id)->whereKey($payment)->firstOrFail();
            app(\App\Http\Controllers\Keuangan\PemasukanController::class)->destroy($payment);
            return response()->json(['success' => true]);
        });
    }

    public function cetakRekap($id)
    {
        $customer = Customer::with(['pemasukans', 'kavling.lokasi', 'lokasiKavling', 'piutangs'])
            ->findOrFail($id);

        $totalHargaKavling = $customer->kavling->sum(function ($kav) {
            return $kav->pivot->hrg_rumah;
        });

        $kodeKavlingGabungan = $customer->kavling->pluck('kode_kavling')->implode(', ');

        $totalTagihan   = $customer->piutangs->sum('nominal') ?? 0;
        $kavlingPertama = $customer->kavling->first();

        $konfigurasi    = KonfigurasiAplikasi::select('nama_perusahaan', 'telp')->first();
        $namaPerusahaan = $konfigurasi->nama_perusahaan ?? 'PT. MULIA ASRI SENTOSA';
        $telp           = $konfigurasi->telp ?? '081250274777';

        $pemasukanBersih = $customer->pemasukans->filter(function ($p) {
            return ! str_starts_with($p->keterangan, 'Biaya ganti nama');
        });

        $lokasi          = $customer->lokasiKavling;
        $pengaturanMedia = KonfigurasiMedia::where('jenis_data', 'kop surat')->first();
        $logoPath        = null;

        if ($pengaturanMedia && $pengaturanMedia->nama_file) {
            $logoPath = public_path('config_media/' . $pengaturanMedia->nama_file);
        }

        $pdf = new TCPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        if ($logoPath && file_exists($logoPath)) {
            $pdf->Image($logoPath, 0, 5, 210);
        }

        $pdf->SetXY(90, 32);

        $pdf->Ln(10);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->SetTextColor(218, 0, 0);
        $pdf->Cell(190, 8, 'TABEL REKAP PEMBAYARAN', 0, 1, 'C');
        $pdf->Ln(3);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Times', '', 9);

        $pdf->Cell(5, 6, '', 0, 0);
        $pdf->Cell(17, 6, 'Nama', 0, 0);
        $pdf->Cell(3, 6, ':', 0, 0);
        $pdf->Cell(45, 6, strtoupper($customer->nama_lengkap), 0, 0);
        $pdf->Cell(5, 6, '', 0, 0);
        $pdf->Cell(17, 6, 'Lokasi', 0, 0);
        $pdf->Cell(3, 6, ':', 0, 0);
        $pdf->Cell(20, 6, $kodeKavlingGabungan, 0, 0);
        $pdf->Cell(5, 6, '', 0, 0);
        $pdf->Cell(25, 6, 'Total Tagihan', 0, 0);
        $pdf->Cell(3, 6, ':', 0, 0);
        $pdf->Cell(30, 6, 'Rp ' . number_format($totalTagihan ?? 0, 0, ',', '.'), 0, 1);

        $pdf->Cell(5, 6, '', 0, 0);
        $pdf->Cell(17, 6, 'No. KTP', 0, 0);
        $pdf->Cell(3, 6, ':', 0, 0);
        $pdf->Cell(45, 6, $customer->no_ktp ?? '-', 0, 0);
        $pdf->Cell(5, 6, '', 0, 0);
        $pdf->Cell(17, 6, 'Luas Tanah', 0, 0);
        $pdf->Cell(3, 6, ':', 0, 0);
        $pdf->Cell(25, 6, ($kavlingPertama->luas_tanah ?? '-') . ' M2', 0, 0);
        $pdf->Cell(25, 6, 'Luas Bangunan', 0, 0);
        $pdf->Cell(3, 6, ':', 0, 0);
        $pdf->Cell(30, 6, ($kavlingPertama->luas_bangunan ?? '-') . ' M2', 0, 1);

        $pdf->Cell(5, 6, '', 0, 0);
        $pdf->Cell(17, 6, 'Alamat', 0, 0);
        $pdf->Cell(3, 6, ':', 0, 0);
        $pdf->Cell(100, 6, $customer->alamat, 0, 1);

        $pdf->Ln(2);
        $pdf->Cell(5, 6, '', 0, 0);
        $pdf->Cell(35, 6, 'Metode Pembayaran', 0, 0);
        $pdf->Cell(35, 6, ': ' . ($customer->jenis_pembelian ?? '-'), 0, 1);

        $pdf->Ln(5);
        $pdf->SetFont('Times', 'B', 9);
        $pdf->SetFillColor(211, 236, 230);
        $pdf->Cell(5, 7, '', 0, 0);
        $pdf->Cell(8, 7, 'No', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Tanggal', 1, 0, 'C', true);
        $pdf->Cell(75, 7, 'Keterangan', 1, 0, 'L', true);
        $pdf->Cell(31, 7, 'Pembayaran', 1, 0, 'C', true);
        $pdf->Cell(31, 7, 'Sisa Pembayaran', 1, 1, 'C', true);

        $pdf->SetFont('Times', '', 9);
        $no   = 1;
        $sisa = $totalTagihan ?? 0;

        foreach ($pemasukanBersih->sortBy('tanggal')->values() as $index => $byr) {
            $sisa        -= $byr->nominal;
            $jumlahBayar  = $byr->nominal;

            $keterangan = explode('#', $byr->keterangan)[0] ?? '';
            $fill       = ($no % 2 == 0) ? [255, 243, 243] : [255, 255, 255];
            $pdf->SetFillColor(...$fill);

            $pdf->Cell(5, 7, '', 0, 0);
            $pdf->Cell(8, 7, $no++, 1, 0, 'C', true);
            $pdf->Cell(30, 7, date('d-m-Y', strtotime($byr->tanggal)), 1, 0, 'C', true);
            $pdf->Cell(75, 7, ($sisa <= 0) ? 'LUNAS' : $keterangan, 1, 0, 'L', true);
            $pdf->Cell(31, 7, 'Rp ' . number_format($jumlahBayar, 0, ',', '.'), 1, 0, 'R', true);
            $pdf->Cell(31, 7, 'Rp ' . number_format(max($sisa, 0), 0, ',', '.'), 1, 1, 'R', true);
        }

        $pdf->Ln(10);
        $pdf->SetFont('', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        $catatan = "Catatan:\n"
            . "- Bukti pembayaran dinyatakan sah apabila disertai kwitansi dari tangan pemilik kavling.\n"
            . "- Apabila ada yang mengaku-ngaku petugas kami \"$namaPerusahaan\" meminta/menagih pembayaran angsuran, harap waspada. HATI-HATI PENIPUAN.\n"
            . "- Konsumen dapat menanyakan atau menghubungi informasi resmi \"$namaPerusahaan\" di nomor $telp.";

        $pdf->MultiCell(0, 0, $catatan, 0, 'L');

        $pdf->Ln(15);
        $pdf->SetFont('Times', '', 9);
        $pdf->Cell(60, 6, 'Mengetahui', 0, 0, 'C');
        $pdf->Cell(70, 6, '', 0, 0);
        $pdf->Cell(60, 6, 'Admin', 0, 1, 'C');
        $pdf->Ln(20);

        $namaMengetahui = $lokasi->nama_penandatangan ?? '';
        $namaAdmin      = $lokasi->nama_admin ?? '';
        $pdf->Cell(60, 6, $namaMengetahui, 0, 0, 'C');
        $pdf->Cell(70, 6, '', 0, 0);
        $pdf->Cell(60, 6, $namaAdmin, 0, 1, 'C');

        $pdf->Output();
        exit;
    }

    public function cetak($id)
    {
        $pembayaran = Pemasukan::with(['customer.piutangs', 'customer.lokasiKavling', 'metode', 'kategori'])
            ->findOrFail($id);

        $logo = DB::table('konfigurasi_media')->where('jenis_data', 'kwitansi')->value('nama_file');

        $customer    = $pembayaran->customer;
        $konfigurasi = KonfigurasiAplikasi::first();

        $totalHutang = $customer->piutangs->sum('nominal') ?? 0;

        // Calculate total payments made up to this specific payment
        $totalAngsuran = Pemasukan::where('id_customer', $customer->id)
            ->where('keterangan', 'NOT LIKE', 'Biaya ganti nama%')
            ->where(function ($query) use ($pembayaran) {
                $query->where('tanggal', '<', $pembayaran->tanggal)
                    ->orWhere(function ($q) use ($pembayaran) {
                        $q->where('tanggal', $pembayaran->tanggal)
                            ->where('id', '<=', $pembayaran->id);
                    });
            })
            ->sum('nominal');

        $sisaHutang = max(0, $totalHutang - $totalAngsuran);

        $data = [
            'perusahaan'     => $konfigurasi->nama_perusahaan ?? 'PT. MULIA ASRI SENTOSA',
            'alamat'         => $konfigurasi->alamat ?? '-',
            'telp'           => 'TELP. ' . ($konfigurasi->telp ?? '081250274777'),
            'tgl_angsuran'   => Carbon::parse($pembayaran->tanggal)->format('d/m/Y'),
            'faktur_no'      => $pembayaran->no_kwitansi ?? '-',
            'no_pelanggan'   => 'CST-' . $customer->id,
            'terima_dari'    => strtoupper($customer->nama_lengkap),
            'sejumlah_uang'  => number_format($pembayaran->nominal, 0, ',', '.'),
            'terbilang'      => $this->terbilang($pembayaran->nominal) . ' Rupiah',
            'items'          => [
                ['no' => 1, 'keterangan' => $pembayaran->keterangan, 'jumlah' => number_format($pembayaran->nominal, 0, ',', '.')],
            ],
            'total'          => number_format($pembayaran->nominal, 0, ',', '.'),
            'total_hutang'   => number_format($totalHutang, 0, ',', '.'),
            'total_angsuran' => number_format($totalAngsuran, 0, ',', '.'),
            'sisa_hutang'    => number_format($sisaHutang, 0, ',', '.'),
            'status'         => ($sisaHutang <= 0) ? 'Lunas' : 'Belum Lunas',
            'jatuh_tempo'    => '-',
            'tgl_cetak'      => Carbon::now()->translatedFormat('d F Y'),
        ];

        $pdf = new TCPDF('L', 'mm', 'A5', true, 'UTF-8', false);

        $pdf->SetCreator($data['perusahaan']);
        $pdf->SetAuthor($data['perusahaan']);
        $pdf->SetTitle('Kwitansi ' . $data['faktur_no']);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins(10, 10, 10, true);
        $pdf->SetAutoPageBreak(false, 0);

        $pdf->AddPage();

        $pdf->SetFont('helvetica', '', 9);

        $pdf->Rect(8, 8, 194, 132, 'D');

        $logoPath = public_path('config_media/' . $logo);

        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 12, 12, 20, 20);
        }

        $pdf->SetXY(35, 12);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(70, 5, $data['perusahaan'], 0, 1, 'L');

        $pdf->SetX(35);
        $pdf->SetFont('helvetica', '', 8);

// WRAP ALAMAT
        $alamat  = $data['alamat'];
        $maxChar = 74;
        $lines   = str_split($alamat, $maxChar);

        foreach ($lines as $line) {
            $pdf->SetX(35);
            $pdf->Cell(70, 4, trim($line), 0, 1, 'L');
        }

        $pdf->SetX(35);
        $pdf->Cell(70, 4, $data['telp'], 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 8.5);

        $xRight = 135;
        $pdf->SetXY($xRight, 12);
        $pdf->Cell(30, 5, 'Tgl Angsuran', 0, 0, 'L');
        $pdf->Cell(3, 5, ':', 0, 0, 'C');
        $pdf->Cell(35, 5, $data['tgl_angsuran'], 0, 1, 'L');

        $pdf->SetX($xRight);
        $pdf->Cell(30, 5, 'Faktur No', 0, 0, 'L');
        $pdf->Cell(3, 5, ':', 0, 0, 'C');
        $pdf->Cell(35, 5, $data['faktur_no'], 0, 1, 'L');

        $pdf->SetX($xRight);
        $pdf->Cell(30, 5, 'No Pelanggan', 0, 0, 'L');
        $pdf->Cell(3, 5, ':', 0, 0, 'C');
        $pdf->Cell(35, 5, $data['no_pelanggan'], 0, 1, 'L');

        $pdf->SetLineWidth(0.3);
        $pdf->Line(10, 30, 202, 30);

        $pdf->SetXY(12, 33);
        $pdf->SetFont('helvetica', '', 8.5);

        $pdf->Cell(32, 5, 'Telah terima dari', 0, 0, 'L');
        $pdf->Cell(3, 5, ':', 0, 0, 'C');
        $pdf->Cell(40, 5, $data['terima_dari'], 0, 0, 'L');

        $xTerbilang = 120;
        $pdf->SetXY($xTerbilang, 31);
        $pdf->SetLineStyle(['width' => 0.2, 'dash' => '2,1', 'color' => [0, 0, 0]]);
        $pdf->RoundedRect($xTerbilang, 31, 80, 10, 3, '1111', 'D');
        $pdf->SetLineStyle(['width' => 0.3, 'dash' => 0, 'color' => [0, 0, 0]]);

        $pdf->SetXY($xTerbilang + 2, 34);
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->Cell(106, 5, $data['terbilang'], 0, 0, 'L');

        $pdf->SetXY(12, 38);
        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->Cell(32, 5, 'Sejumlah uang', 0, 0, 'L');
        $pdf->Cell(3, 5, ':', 0, 0, 'C');
        $pdf->Cell(40, 5, $data['sejumlah_uang'], 0, 0, 'L');

        $pdf->Line(10, 45, 202, 45);

        $pdf->SetXY(12, 46);
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->Cell(14, 5, 'NO', 0, 0, 'L');
        $pdf->Cell(140, 5, 'K E T E R A N G A N', 0, 0, 'L');
        $pdf->Cell(36, 5, 'JUMLAH', 0, 0, 'R');

        $pdf->Line(10, 52, 202, 52);

        $pdf->SetFont('helvetica', '', 8.5);
        $yItem = 53;

        foreach ($data['items'] as $item) {
            $pdf->SetXY(12, $yItem);
            $pdf->Cell(14, 5, $item['no'], 0, 0, 'L');
            $pdf->Cell(140, 5, $item['keterangan'], 0, 0, 'L');
            $pdf->Cell(36, 5, $item['jumlah'], 0, 0, 'R');
            $yItem += 6;
        }

        $y = 95;

        $pdf->Line(10, $y - 2, 202, $y - 2);

        $pdf->SetXY(12, $y);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(154, 5, 'T O T A L :', 0, 0, 'R');
        $pdf->Cell(36, 5, $data['total'], 0, 0, 'R');

        $y += 6;
        $pdf->Line(10, $y, 202, $y);

        $y += 2;

        $pdf->SetFont('helvetica', '', 8.5);

        $xL  = 12;

        $pdf->SetXY($xL, $y);
        $pdf->Cell(28, 5, 'Total Hutang', 0, 0, 'L');
        $pdf->Cell(4, 5, ':', 0, 0, 'C');
        $pdf->Cell(35, 5, $data['total_hutang'], 0, 1, 'L');

        $y += 5;

        $pdf->SetXY($xL, $y);
        $pdf->Cell(28, 5, 'Total Angsuran', 0, 0, 'L');
        $pdf->Cell(4, 5, ':', 0, 0, 'C');
        $pdf->Cell(35, 5, $data['total_angsuran'], 0, 1, 'L');

        $y += 5;

        $pdf->SetXY($xL, $y);
        $pdf->Cell(28, 5, 'Sisa Hutang', 0, 0, 'L');
        $pdf->Cell(4, 5, ':', 0, 0, 'C');
        $pdf->Cell(35, 5, $data['sisa_hutang'], 0, 1, 'L');

        $y += 6;

        $pdf->SetXY($xL, $y);
        $pdf->Cell(28, 5, 'Status', 0, 0, 'L');
        $pdf->Cell(4, 5, ':', 0, 0, 'C');
        $pdf->Cell(35, 5, $data['status'], 0, 1, 'L');

        $y += 5;

        $pdf->SetXY($xL, $y);
        $pdf->Cell(28, 5, 'Jatuh Tempo', 0, 0, 'L');
        $pdf->Cell(4, 5, ':', 0, 0, 'C');
        $pdf->Cell(35, 5, $data['jatuh_tempo'], 0, 1, 'L');

        $xPerh = 85;
        $pdf->SetXY($xPerh, $y - 10);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(40, 5, 'Perhatian :', 0, 0, 'L');

        $pdf->SetLineWidth(0.3);
        $pdf->Rect($xPerh, $y - 4, 60, 14, 'D');

        $xR = 155;
        $pdf->SetXY($xR, $y - 15);
        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->Cell(45, 5, $data['tgl_cetak'], 0, 0, 'R');

        $pdf->SetXY($xR, $y + 5);
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->Cell(45, 5, 'Melda Shantika', 0, 0, 'R');

        $safeFaktur = preg_replace('/[^A-Za-z0-9_\-]/', '_', $pembayaran->no_kwitansi ?: $pembayaran->id);
        $filename   = 'kwitansi_' . $pembayaran->id . '_' . $safeFaktur . '.pdf';
        $saveDir    = public_path('kwitansi');

        if (! is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        $pdfContent = $pdf->Output($filename, 'S');
        file_put_contents($saveDir . DIRECTORY_SEPARATOR . $filename, $pdfContent);

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    private function terbilang($angka)
    {
        $angka = abs($angka);
        $baca  = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $hasil = '';

        if ($angka < 12) {
            $hasil = ' ' . $baca[$angka];
        } elseif ($angka < 20) {
            $hasil = $this->terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            $hasil = $this->terbilang($angka / 10) . ' Puluh' . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            $hasil = ' Seratus' . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $hasil = $this->terbilang($angka / 100) . ' Ratus' . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $hasil = ' Seribu' . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $hasil = $this->terbilang($angka / 1000) . ' Ribu' . $this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $hasil = $this->terbilang($angka / 1000000) . ' Juta' . $this->terbilang($angka % 1000000);
        }

        return trim($hasil);
    }

    private function normalizePhoneNumber($phoneNumber)
    {
        $phoneNumber = preg_replace('/\D+/', '', $phoneNumber);

        if (str_starts_with($phoneNumber, '0')) {
            return '62' . substr($phoneNumber, 1);
        }

        if (str_starts_with($phoneNumber, '8')) {
            return '62' . $phoneNumber;
        }

        return $phoneNumber;
    }

    private function sendWaRequest($url, array $payload)
    {
        try {
            if (str_contains($url, '{')) {
                $replace = [];
                foreach ($payload as $key => $value) {
                    $replace['{' . $key . '}'] = rawurlencode((string) $value);
                }

                $response = Http::timeout(30)->acceptJson()->get(strtr($url, $replace));
            } elseif (isset($payload['clientId'])) {
                $response = Http::timeout(30)->acceptJson()->post($url, $payload);
            } else {
                $response = Http::timeout(30)->acceptJson()->post($url, $payload);
            }

            $responseData = $response->json();
            $status       = strtolower((string) data_get($responseData, 'status', ''));
            $successValue = data_get($responseData, 'success', true);
            $okValue      = data_get($responseData, 'ok', true);
            $message      = data_get($responseData, 'message')
                ?? data_get($responseData, 'detail')
                ?? data_get($responseData, 'reason')
                ?? data_get($responseData, 'error')
                ?? data_get($responseData, 'msg')
                ?? $response->body();

            $success = $response->successful()
                && $successValue !== false
                && $okValue !== false
                && ! in_array($status, ['false', 'failed', 'fail', 'error', 'gagal'], true);

            return [
                'success' => $success,
                'message' => $message ?: ($success ? 'Berhasil' : 'Gagal'),
                'data'    => $responseData,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    private function resolveFileApiUrl($url)
    {
        if (str_contains($url, '{url}')) {
            return $url;
        }

        if (str_contains($url, '/messages/text')) {
            return str_replace('/messages/text', '/messages/pdf', $url);
        }

        if (str_contains($url, '/messages/pdf')) {
            return $url;
        }

        $parts = parse_url($url);
        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            return $url;
        }

        $path = $parts['path'] ?? '';
        $base = $parts['scheme'] . '://' . $parts['host'];

        if (! empty($parts['port'])) {
            $base .= ':' . $parts['port'];
        }

        if (preg_match('#/clients/[^/]+/?$#', $path)) {
            return rtrim($base, '/') . '/messages/pdf';
        }

        if ($path && $path !== '/') {
            return $url;
        }

        return rtrim($base, '/') . '/messages/pdf';
    }

    private function resolveTextApiUrl($url)
    {
        if (str_contains($url, '{')) {
            return $url;
        }

        if (str_contains($url, '/messages/text')) {
            return $url;
        }

        if (str_contains($url, '/messages/pdf')) {
            return str_replace('/messages/pdf', '/messages/text', $url);
        }

        $parts = parse_url($url);
        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            return rtrim($url, '/') . '/messages/text';
        }

        $path = $parts['path'] ?? '';
        $base = $parts['scheme'] . '://' . $parts['host'];

        if (! empty($parts['port'])) {
            $base .= ':' . $parts['port'];
        }

        if (preg_match('#/clients/([^/]+)/?$#', $path)) {
            return rtrim($base, '/') . '/messages/text';
        }

        if ($path && $path !== '/') {
            return $url;
        }

        return rtrim($base, '/') . '/messages/text';
    }

    private function extractClientIdFromUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        if (preg_match('#/clients/([^/]+)/?$#', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function detailTagihan(Request $request, $id)
    {
        if ($request->ajax()) {
            $tagihanList  = Piutang::where('id_customer', $id)->orderBy('id')->get();
            $totalTagihan = $tagihanList->sum('nominal');
            $firstId      = $tagihanList->first() ? $tagihanList->first()->id : null;

            return DataTables::of($tagihanList)
                ->addIndexColumn()
                ->addColumn('jumlah_tagihan', function ($row) {
                    return '<div class="d-flex justify-content-between">
                        <span>Rp.</span>
                        <span>' . number_format($row->nominal, 0, ',', '.') . '</span>
                    </div>';
                })
                ->addColumn('action', function ($row) use ($id, $firstId) {
                    $deleteUrl = route('pembayaran.delete-tagihan', $row->id);

                    if ($row->id == $firstId) {
                        return '<form class="formHargaRumah" data-id="' . $id . '">' .
                        csrf_field() .
                            '<button type="submit" class="btn btn-warning text-white btn-sm ms-1 btn-update-harga">
                            <span class="swal-btn-text">Update</span>
                        </button>' .
                            '</form>';
                    } else {
                        return '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="delete-tagihan btn btn-danger btn-sm">Hapus</button></form>';
                    }
                })
                ->rawColumns(['action', 'jumlah_tagihan'])
                ->with('total_tagihan', $totalTagihan)
                ->with('total_tagihan_formatted', number_format($totalTagihan, 0, ',', '.'))
                ->make(true);
        }
    }

    public function tambahTagihan(Request $request, $id)
    {
        $request->merge([
            'nominal'           => $request->nominal ? (int) str_replace(['.', ','], '', $request->nominal) : 0,
        ]);

        $rules = [
            'id_kategori' => 'required',
            'deskripsi'   => 'required',
            'nominal'     => 'required',
        ];

        $messages = [
            'id_kategori.required' => 'Kategori Transaksi wajib dipilih.',
            'deskripsi.required'   => 'Deskripsi tagihan wajib diisi.',
            'nominal.required'     => 'Nominal wajib diisi.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {

            $cust = Customer::find($id);

            Piutang::create([
                'id_customer'           => $id,
                'id_bank'               => $cust->id_bank,
                'tanggal_piutang'       => Carbon::now(),
                'deskripsi'             => $request->deskripsi,
                'id_kategori_transaksi' => $request->id_kategori,
                'nominal'               => $request->nominal,
                'status'                => 1,
                'terbayar'              => 0,
                'sisa_bayar'            => $request->nominal,
            ]);

            $totalTagihan = Piutang::where('id_customer', $id)->sum('nominal');
            $sisaBayar    = Piutang::where('id_customer', $id)->sum('sisa_bayar');

            DB::commit();

            return response()->json([
                'success'                 => true,
                'total_tagihan_formatted' => number_format($totalTagihan, 0, ',', '.'),
                'sisa_bayar_formatted'    => number_format($sisaBayar, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan tagihan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function UpdateHargaRumah($id)
    {
        DB::beginTransaction();
        try {
            $cust = Customer::with('kavling')->findOrFail($id);

            $kavlingIds = $cust->kavling->pluck('id')->toArray();

            $kavlingPetaData = KavlingPeta::whereIn('id', $kavlingIds)->get();

            $totalHargaKavlingBaru = $kavlingPetaData->sum('hrg_jual');

            foreach ($kavlingPetaData as $kav) {
                DB::table('transaksi_kavling')
                    ->where('id_customer', $cust->id)
                    ->where('id_kavling', $kav->id)
                    ->update(['hrg_rumah' => $kav->hrg_jual]);
            }

            $piutangUtama = Piutang::where('id_customer', $id)->first();

            if (! $piutangUtama) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Piutang utama untuk customer ini tidak ditemukan.',
                ], 404);
            }

            $terbayarLama  = $piutangUtama->terbayar ?? 0;
            $sisaBayarBaru = max(0, $totalHargaKavlingBaru - $terbayarLama);

            $piutangUtama->update([
                'nominal'    => $totalHargaKavlingBaru,
                'sisa_bayar' => $sisaBayarBaru,
            ]);

            if ($terbayarLama >= $totalHargaKavlingBaru) {
                $piutangUtama->update(['status' => 2]); // Lunas
            } else {
                $piutangUtama->update(['status' => 1]); // Belum Lunas
            }

            $totalTagihan = Piutang::where('id_customer', $id)->sum('nominal');
            $sisaBayar    = Piutang::where('id_customer', $id)->sum('sisa_bayar');

            DB::commit();

            return response()->json([
                'success'                 => true,
                'message'                 => 'Harga rumah berhasil diupdate.',
                'total_tagihan_formatted' => number_format($totalTagihan, 0, ',', '.'),
                'sisa_bayar_formatted'    => number_format($sisaBayar, 0, ',', '.'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate harga rumah.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function DeleteTagihan($id)
    {
        DB::beginTransaction();
        try {
            $tagihan     = Piutang::findOrFail($id);
            $id_customer = $tagihan->id_customer;
            $tagihan->delete();

            Pemasukan::where('id_piutang', $id)
                ->where('keterangan', 'NOT LIKE', 'Biaya ganti nama%')
                ->delete();

            $totalTagihan = Piutang::where('id_customer', $id_customer)->sum('nominal');
            $jumlahBayar  = Piutang::where('id_customer', $id_customer)->sum('terbayar');
            $sisaBayar    = Piutang::where('id_customer', $id_customer)->sum('sisa_bayar');

            DB::commit();

            return response()->json([
                'success'                 => true,
                'message'                 => 'Tagihan berhasil dihapus.',
                'total_tagihan_formatted' => number_format($totalTagihan, 0, ',', '.'),
                'jumlah_bayar_formatted'  => number_format($jumlahBayar, 0, ',', '.'),
                'sisa_bayar_formatted'    => number_format($sisaBayar, 0, ',', '.'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tagihan.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function detailPemasukan(Request $request, $id)
    {
        Carbon::setLocale('id');

        if ($request->ajax()) {
            $templatePesan = TemplatePesan::where('jenis_pesan', 'kwitansi')->first()
                ?? TemplatePesan::first();

            $data = Pemasukan::with(['kategori', 'customer.kavling'])
                ->where('id_customer', $id)
                ->where('keterangan', 'NOT LIKE', 'Biaya ganti nama%')
                ->get();

            foreach ($data as $item) {
                $deleteUrl              = route('pembayaran.delete-pemasukan', $item->id);
                $cetakUrl               = route('pembayaran.cetak', $item->id);
                $safeFaktur             = preg_replace('/[^A-Za-z0-9_\-]/', '_', $item->no_kwitansi ?: $item->id);
                $pdfFilename            = 'kwitansi_' . $item->id . '_' . $safeFaktur . '.pdf';
                $pdfUrl                 = request()->getSchemeAndHttpHost() . '/kwitansi/' . $pdfFilename;
                $pdfFileExists          = file_exists(public_path('kwitansi/' . $pdfFilename));
                $customer               = $item->customer;
                $kodeKavlingGabungan    = $customer?->kavling?->pluck('kode_kavling')->implode(', ') ?: '-';
                $isiPesan               = $templatePesan->isi_template ?? '';
                $isiPesan               = strtr($isiPesan, [
                    '{nama}'             => $customer->nama_lengkap ?? '-',
                    '{nama_customer}'    => $customer->nama_lengkap ?? '-',
                    '[[nama_customer]]'  => $customer->nama_lengkap ?? '-',
                    '{kode_kavling}'     => $kodeKavlingGabungan,
                    '[[kode_kavling]]'   => $kodeKavlingGabungan,
                    '{no_kwitansi}'      => $item->no_kwitansi ?? '-',
                    '{nominal}'          => 'Rp. ' . number_format($item->nominal, 0, ',', '.'),
                    '{tanggal}'          => Carbon::parse($item->tanggal)->translatedFormat('d F Y'),
                    '{jenis}'            => $item->kategori->kategori ?? '-',
                ]);
                $item->tanggal          = Carbon::parse($item->tanggal)->translatedFormat('d F Y');
                $item->kategori         = $item->kategori->kategori ?? '-';
                $item->jumlah_formatted = '
                    <div class="d-flex justify-content-between">
                        <span>Rp.</span>
                        <span>' . number_format($item->nominal, 0, ',', '.') . '</span>
                    </div>';

                $action = '<div class="action-pemasukan"><form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">'
                . csrf_field()
                . method_field('DELETE')
                    . '<button type="submit" class="delete-pemasukan btn btn-danger btn-sm btn-action-xs">Hapus</button></form></div>';

                if (! in_array($item->id_kategori_transaksi, [4])) {
                    $action = '<div class="action-pemasukan">
                    <a class="btn btn-sm btn-primary btn-action-xs btn-cetak-pemasukan"
                        href="' . e($cetakUrl) . '"
                        target="_blank"
                        data-id="' . e($item->id) . '">Cetak</a>
                    <button type="button"
                        class="btn btn-sm btn-success btn-action-xs btn-kirim-pemasukan' . ($pdfFileExists ? '' : ' d-none') . '"
                        data-bs-toggle="modal"
                        data-bs-target="#modalKirimKwitansi"
                        data-id="' . e($item->id) . '"
                        data-nama="' . e($customer->nama_lengkap ?? '-') . '"
                        data-no-wa="' . e($customer->no_wa ?? $customer->no_telp ?? '') . '"
                        data-pesan="' . e($isiPesan) . '"
                        data-pdf-url="' . e($pdfUrl) . '">Kirim</button>
                    <form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">'
                        . csrf_field()
                        . method_field('DELETE')
                        . '<button type="submit" class="delete-pemasukan btn btn-danger btn-sm btn-action-xs">Hapus</button></form>
                    </div>';
                }

                $item->action = $action;
            }

            $total = $data->sum('nominal');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', fn($item) => $item->tanggal)
                ->addColumn('keterangan', fn($item) => $item->keterangan)
                ->addColumn('kategori', fn($item) => $item->kategori)
                ->addColumn('jumlah', fn($item) => $item->jumlah_formatted)
                ->addColumn('action', fn($item) => $item->action)
                ->with('total_pemasukan_formatted', number_format($total, 0, ',', '.'))
                ->rawColumns(['action', 'jumlah'])
                ->make(true);
        }
    }

    public function kirimKwitansi(Request $request)
    {
        $request->validate([
            'nama_customer' => 'required',
            'no_wa'         => 'required',
            'isi_pesan'     => 'required',
            'pdf_url'       => 'required|url',
        ], [
            'nama_customer.required' => 'Nama customer wajib diisi.',
            'no_wa.required'         => 'No WA wajib diisi.',
            'isi_pesan.required'     => 'Isi pesan wajib diisi.',
            'pdf_url.required'       => 'File PDF wajib tersedia.',
            'pdf_url.url'            => 'URL PDF tidak valid.',
        ]);

        $konfigurasiWa = KonfigurasiWa::first();

        $clientId = $konfigurasiWa ? ($this->extractClientIdFromUrl($konfigurasiWa->url_api) ?: $konfigurasiWa->number_key) : null;

        if (! $konfigurasiWa || ! $konfigurasiWa->url_api || ! $konfigurasiWa->api_key || ! $clientId) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan koneksi WA belum lengkap.',
            ], 422);
        }

        $phoneNo = $this->normalizePhoneNumber($request->no_wa);

        $textResponse = $this->sendWaRequest($this->resolveTextApiUrl($konfigurasiWa->url_api), [
            'clientId'   => $clientId,
            'to'         => $phoneNo,
            'text'       => $request->isi_pesan,
            'api_key'    => $konfigurasiWa->api_key,
            'number_key' => $konfigurasiWa->number_key,
            'phone_no'   => $phoneNo,
            'no_wa'      => $phoneNo,
            'message'    => $request->isi_pesan,
        ]);

        if (! $textResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan gagal dikirim: ' . $textResponse['message'],
            ], 422);
        }

        $fileResponse = $this->sendWaRequest($this->resolveFileApiUrl($konfigurasiWa->url_api), [
            'clientId'    => $clientId,
            'to'          => $phoneNo,
            'phone_no'    => $phoneNo,
            'documentUrl' => $request->pdf_url,
            'fileName'    => basename(parse_url($request->pdf_url, PHP_URL_PATH) ?: 'kwitansi.pdf'),
            'caption'     => 'Kwitansi',
            'api_key'     => $konfigurasiWa->api_key,
            'number_key'  => $konfigurasiWa->number_key,
            'message'     => 'Kwitansi',
            'url'         => $request->pdf_url,
        ]);

        if (! $fileResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan terkirim, tetapi PDF gagal dikirim: ' . $fileResponse['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesan dan PDF berhasil dikirim.',
        ]);
    }

    public function tambahPemasukan(Request $request, $id)
    {
        $rules = [
            'tanggal_pembayaran'    => 'required|date',
            'id_kategori_transaksi' => 'required',
            'id_tagihan'            => 'required_if:id_kategori_transaksi,102',
            'nominal_bayar'         => 'required',
            'keterangan_pembayaran' => 'required',
            'id_metode'             => 'nullable',
            'file'                  => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:2048',
        ];

        $messages = [
            'tanggal_pembayaran.required'    => 'Tanggal Pembayaran wajib diisi.',
            'tanggal_pembayaran.date'        => 'Format Tanggal Pembayaran tidak valid.',
            'id_kategori_transaksi.required' => 'Kategori Transaksi wajib diisi.',
            'id_tagihan.required_if'         => 'Tagihan wajib dipilih.',
            'nominal_bayar.required'         => 'Nominal wajib diisi.',
            'keterangan_pembayaran.required' => 'Keterangan wajib diisi.',
            'file.file'                      => 'file harus berupa file.',
            'file.mimes'                     => 'Format file harus jpeg, png, jpg, atau pdf.',
            'file.max'                       => 'Ukuran file maksimal 2MB.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $ext  = $file->getClientOriginalExtension();

                $filename = Str::random(25) . '.' . $ext;
                $file->move(public_path('assets/keuangan/pemasukan/'), $filename);
            }

            $cust = Customer::find($id);

            if (! $cust) {
                return response()->json(['error' => 'Customer tidak ditemukan.'], 404);
            }

            $jenis = $request->id_kategori_transaksi;

            $no_kwitansi = '';

            if ($jenis != 4) {
                $tahun       = Carbon::now()->format('Y');
                $bulan       = Carbon::now()->format('n');
                $bulanRomawi = $this->bulanRomawi($bulan);

                $lokasiSingkat = LokasiKavling::where('id', $cust->id_lokasi)
                    ->value('nama_singkat') ?? 'XXX';

                $lastKwitansi = Pemasukan::whereYear('tanggal', $tahun)
                    ->where('keterangan', 'NOT LIKE', 'Biaya ganti nama%')
                    ->where('id_lokasi', $cust->id_lokasi)
                    ->orderBy('no_kwitansi', 'desc')
                    ->first();

                if ($lastKwitansi) {

                    $lastNumber = (int) substr($lastKwitansi->no_kwitansi, 0, 4);
                    $newNumber  = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                } else {
                    $newNumber = '0001';
                }

                $no_kwitansi = $newNumber . '/' . $lokasiSingkat . '/' . $bulanRomawi . '/' . $tahun;
            }

            Pemasukan::create([
                'tanggal'               => $request->tanggal_pembayaran,
                'id_customer'           => $id,
                'id_bank'               => $cust->id_bank,
                'id_piutang'            => $request->id_tagihan ?? 0,
                'id_kategori_transaksi' => $request->id_kategori_transaksi,
                'no_kwitansi'           => $no_kwitansi,
                'nominal'               => str_replace('.', '', $request->nominal_bayar),
                'keterangan'            => $request->keterangan_pembayaran,
                'id_metode'             => $request->id_metode ?? 2,
                'lampiran'              => $filename ?? '',
            ]);

            $nominalBayar = (int) str_replace('.', '', $request->nominal_bayar);

            if ($jenis == 102) {
                $piutang = Piutang::find($request->id_tagihan);
                if ($piutang) {
                    if ($nominalBayar > $piutang->sisa_bayar) {
                        DB::rollBack();

                        return response()->json([
                            'success' => false,
                            'message' => 'Nominal pembayaran melebihi sisa tagihan. Sisa tagihan: Rp. ' . number_format($piutang->sisa_bayar, 0, ',', '.'),
                        ], 422);
                    }

                    $newSisaBayar = $piutang->sisa_bayar - $nominalBayar;
                    $updateData   = [
                        'terbayar'   => $piutang->terbayar + $nominalBayar,
                        'sisa_bayar' => $newSisaBayar,
                    ];

                    if ($newSisaBayar <= 0) {
                        $updateData['status']        = 2;
                        $updateData['tgl_pelunasan'] = Carbon::now();
                    }

                    $piutang->update($updateData);
                }
            } else {
                $piutang = Piutang::where('id_customer', $id)->first();

                if ($piutang) {
                    if ($nominalBayar > $piutang->sisa_bayar) {
                        DB::rollBack();

                        return response()->json([
                            'success' => false,
                            'message' => 'Nominal pembayaran melebihi sisa tagihan. Sisa tagihan: Rp. ' . number_format($piutang->sisa_bayar, 0, ',', '.'),
                        ], 422);
                    }

                    $newSisaBayar = $piutang->sisa_bayar - $nominalBayar;
                    $updateData   = [
                        'terbayar'   => $piutang->terbayar + $nominalBayar,
                        'sisa_bayar' => $newSisaBayar,
                    ];

                    if ($newSisaBayar <= 0) {
                        $updateData['status']        = 2;
                        $updateData['tgl_pelunasan'] = Carbon::now();
                    }

                    $piutang->update($updateData);
                }
            }

            $totalTagihan = Piutang::where('id_customer', $id)->sum('nominal');
            $terbayar     = Piutang::where('id_customer', $id)->sum('terbayar');
            $sisaBayar    = Piutang::where('id_customer', $id)->sum('sisa_bayar');

            // Automatic Status Update to Lunas (ID 22)
            if ($sisaBayar <= 0 && $totalTagihan > 0) {
                $cust->update(['id_status_progres' => 22]);
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'jumlah_bayar'  => number_format($terbayar, 0, ',', '.'),
                'total_tagihan' => number_format($totalTagihan, 0, ',', '.'),
                'sisa_bayar'    => number_format($sisaBayar, 0, ',', '.'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pemasukan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function DeletePemasukan($id)
    {
        DB::beginTransaction();
        try {
            $pemasukan = Pemasukan::where('keterangan', 'NOT LIKE', 'Biaya ganti nama%')
                ->find($id);

            if (! $pemasukan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pemasukan tidak ditemukan atau tidak dapat dihapus.',
                ], 404);
            }

            $piutang = Piutang::find($pemasukan->id_piutang);

            if (! $piutang && $pemasukan->id_piutang == 0) {
                $piutang = Piutang::where('id_customer', $pemasukan->id_customer)->first();
            }

            if ($piutang) {
                $newSisaBayar = $piutang->sisa_bayar + $pemasukan->nominal;
                $newTerbayar  = $piutang->terbayar - $pemasukan->nominal;

                $newStatus = $newSisaBayar <= 0 ? 2 : 1;

                $updateData = [
                    'terbayar'   => $newTerbayar,
                    'sisa_bayar' => $newSisaBayar,
                    'status'     => $newStatus,
                ];

                if ($newStatus == 1) {
                    $updateData['tgl_pelunasan'] = null;
                }

                $piutang->update($updateData);
            }

            $id_customer = $pemasukan->id_customer;

            $pemasukan->delete();

            $totalTagihan = Piutang::where('id_customer', $id_customer)->sum('nominal');
            $terbayar     = Piutang::where('id_customer', $id_customer)->sum('terbayar');
            $sisaBayar    = Piutang::where('id_customer', $id_customer)->sum('sisa_bayar');

            // Automatic Status Revert from Lunas to Akad (ID 3)
            if ($sisaBayar > 0) {
                $customerObj = Customer::find($id_customer);
                if ($customerObj && $customerObj->id_status_progres == 22) {
                    $customerObj->update(['id_status_progres' => 3]);
                }
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'message'       => 'Pemasukan berhasil dihapus.',
                'jumlah_bayar'  => number_format($terbayar, 0, ',', '.'),
                'total_tagihan' => number_format($totalTagihan, 0, ',', '.'),
                'sisa_bayar'    => number_format($sisaBayar, 0, ',', '.'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pemasukan.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function print($id)
    {
        $pembayaran = Pemasukan::with(['customer', 'metode', 'kategori'])->where('id', $id)
            ->where('keterangan', 'NOT LIKE', 'Biaya ganti nama%')->firstOrFail();
        $nasabah = $pembayaran->customer;
        $lokasi  = LokasiKavling::find($nasabah->id_lokasi ?? null)->first();
        $kavling = KavlingPeta::find($nasabah->id_kavling ?? null)->first();

        $noKwitansi  = $pembayaran->no_kwitansi ?? '-';
        $nama        = $nasabah->nama_lengkap ?? '-';
        $alamat      = $nasabah->alamat_ktp ?? $nasabah->alamat_domisili ?? '-';
        $jumlah      = $pembayaran->nominal ?? 0;
        $terbilang   = '#' . strtoupper($this->terbilang($jumlah)) . ' Rupiah#';
        $namaKavling = $lokasi->nama_kavling ?? '-';
        $tipe        = $kavling->tipe_bangunan ?? '-';
        $blokNomor   = '-';
        if ($lokasi) {
            if ($lokasi->is_cluster) {
                $blokNomor = ($kavling->cluster ?? '-') . '-' . ($kavling->no ?? '-');
            } else {
                $blokNomor = $kavling->kode_kavling ?? '-';
            }
        }
        $rumahId         = $kavling->id_rumah_sikumbang ?? '-';
        $hargaJual       = $kavling->hrg_jual ?? 0;
        $kotaTtd         = $lokasi->kota_penandatangan ?? '-';
        $tanggal         = $pembayaran->tanggal ? Carbon::parse($pembayaran->tanggal)->translatedFormat('d F Y') : '-';
        $jenisPembayaran = $pembayaran->kategori->kategori ?? 'Cicilan Pribadi';
        $metodeBayar     = $pembayaran->metode->jenis_bayar ?? 'CASH';

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Kwitansi');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(20, 0, 0);    // Mengatur margin seperti dot matrix
        $pdf->SetAutoPageBreak(false); // Nonaktifkan auto page break
        $pdf->AddPage();
        $pdf->SetTextColor(0, 0, 0);

        // Tambahkan space atas seperti dot matrix
        $pdf->Ln(40);

        // Header - TANDA TERIMA dengan underline
        $pdf->SetFont('Helvetica', 'BU', 16);
        $pdf->Cell(170, 6, 'TANDA TERIMA', 0, 1, 'C');

        // Nomor kwitansi
        $pdf->SetFont('Helvetica', 'I', 12);
        $pdf->Cell(170, 6, $noKwitansi, 0, 1, 'C');

        // Detail penerima
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(100, 5, 'Sudah diterima dari : ', 0, 1, 'L');

        $pdf->Cell(20, 5, '', 0, 0, 'L');
        $pdf->Cell(25, 5, 'Nama', 0, 0, 'L');
        $pdf->Cell(60, 5, ' : ' . $nama, 0, 1, 'L');

        $pdf->Cell(20, 5, '', 0, 0, 'L');
        $pdf->Cell(25, 5, 'Alamat', 0, 0, 'L');
        $pdf->Cell(100, 5, ' : ' . $alamat, 0, 1, 'L');

        // Jumlah uang
        $pdf->Ln(3);
        $pdf->Cell(20, 5, 'Uang sejumlah Rp. ' . number_format($jumlah, 0, ',', '.') . ' (' . $terbilang . ')', 0, 1, 'L');
        $pdf->Cell(20, 5, 'Untuk pembayaran ' . $jenisPembayaran . ' atas pembelian rumah di : ', 0, 1, 'L');

        // Detail properti
        $pdf->Ln(1);
        $pdf->Cell(20, 4, '', 0, 0, 'L');
        $pdf->Cell(25, 4, 'Perumahan', 0, 0, 'L');
        $pdf->Cell(60, 4, ' : ' . $namaKavling, 0, 1, 'L');

        $pdf->Cell(20, 4, '', 0, 0, 'L');
        $pdf->Cell(25, 4, 'Type', 0, 0, 'L');
        $pdf->Cell(60, 4, ' : ' . $tipe, 0, 1, 'L');

        $pdf->Cell(20, 4, '', 0, 0, 'L');
        $labelBlok = $lokasi->is_cluster ? 'Cluster / Nomor' : 'Blok / Nomor';
        $pdf->Cell(25, 4, $labelBlok, 0, 0, 'L');
        $pdf->Cell(60, 4, ' : ' . $blokNomor, 0, 1, 'L');

        $pdf->Cell(20, 4, '', 0, 0, 'L');
        $pdf->Cell(25, 4, 'Rumah ID', 0, 0, 'L');
        $pdf->Cell(60, 4, ' : ' . $rumahId, 0, 1, 'L');

        $pdf->Cell(20, 4, '', 0, 0, 'L');
        $pdf->Cell(25, 4, 'Harga Jual', 0, 0, 'L');
        $pdf->Cell(60, 4, ' : Rp. ' . number_format($hargaJual, 0, ',', '.'), 0, 1, 'L');

        // Tanggal dan keterangan
        $pdf->SetFont('Helvetica', 'B', 8.5);
        $pdf->Cell(20, 4, '', 0, 1, 'L');
        $pdf->Cell(20, 4, '', 0, 0, 'L');
        $pdf->Cell(100, 4, '', 0, 0, 'L');
        $pdf->Cell(55, 4, $kotaTtd . ', ' . $tanggal, 0, 1, 'C');

        $pdf->Ln(2);

        // Footer dengan tanda tangan
        $pdf->SetFont('Helvetica', '', 8.5);
        $pdf->Cell(35, 5, 'Keterangan : ', 0, 0, 'L');
        $pdf->Cell(45, 5, 'Kasir', 0, 0, 'C');
        $pdf->Cell(45, 5, 'Penyetor', 0, 0, 'C');
        $pdf->Cell(45, 5, 'Customer Service', 0, 1, 'C');

        $pdf->Cell(35, 5, $metodeBayar, 0, 0, 'L');
        $pdf->Cell(100, 5, '', 0, 0, 'L');
        $pdf->Ln(20);

        // Garis untuk tanda tangan
        $pdf->SetLineWidth(0.2);
        $pdf->Line(60, 135, 95, 135);
        $pdf->Line(105, 135, 140, 135);
        $pdf->Line(150, 135, 185, 135);

        // Catatan kaki
        $pdf->SetFont('Helvetica', 'I', 8.5);
        $pdf->Cell(20, 5, 'NB : Kwitansi ini sah, apabila ada cap perusahaan dan tanda tangan kasir.', 0, 1, 'L');

        return response($pdf->Output('kwitansi.pdf', 'I'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    private function bulanRomawi($bulan)
    {
        $romawi = [
            1  => 'I',
            2  => 'II',
            3  => 'III',
            4  => 'IV',
            5  => 'V',
            6  => 'VI',
            7  => 'VII',
            8  => 'VIII',
            9  => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $romawi[(int) $bulan] ?? '';
    }

    public function rekapPembayaran(Request $request)
    {
        $pembayaran = KategoriTransaksi::where('id', 4)->get();
        $lokasi     = LokasiKavling::orderBy('id', 'asc')->get();

        if ($request->ajax()) {
            $data = KavlingPeta::with(['customer', 'lokasi'])
                ->whereHas('lokasi', function ($q) use ($request) {
                    if ($request->lokasi_id) {
                        $q->where('id', $request->lokasi_id);
                    }
                })
                ->when($request->status == 1, function ($q) {
                    $q->whereHas('customer');
                })
                ->orderBy('kode_kavling', 'asc')
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('customer', function ($row) {
                    return optional($row->customer)->nama_lengkap ?? '';
                })
                ->addColumn('lokasi', function ($row) {
                    $namaLokasi = optional($row->lokasi)->nama_kavling ?? '-';

                    if (optional($row->lokasi)->is_cluster == 1) {
                        $kodeKavling = $row->cluster . '-' . $row->no ?? '-';
                    } else {
                        $kodeKavling = $row->kode_kavling ?? '-';
                    }

                    return '<strong>' . $namaLokasi . '</strong><br> ' . $kodeKavling;
                })
                ->editColumn('hrg_jual', function ($row) {
                    return 'Rp. ' . number_format($row->hrg_jual, 0, ',', '.');
                })
                ->addColumn('pembayaran', function ($row) {
                    $customerId = optional($row->customer)->id;

                    if (! $customerId) {
                        return 'Rp. 0';
                    }

                    $jumlahBayar = Pemasukan::where('id_customer', $customerId)
                        ->where('keterangan', 'NOT LIKE', 'Biaya ganti nama%')
                        ->where('id_kategori_transaksi', '!=', 4)
                        ->sum('nominal');

                    return 'Rp. ' . number_format($jumlahBayar, 0, ',', '.');
                })
                ->addColumn('pencairan', function ($row) {
                    $customerId = optional($row->customer)->id;

                    if (! $customerId) {
                        return 'Rp. 0';
                    }

                    $pencairan = Pemasukan::where('id_customer', $customerId)
                        ->where('keterangan', 'NOT LIKE', 'Biaya ganti nama%')
                        ->where('id_kategori_transaksi', 4)
                        ->sum('nominal');

                    return 'Rp. ' . number_format($pencairan, 0, ',', '.');
                })
                ->addColumn('sbum', function ($row) {
                    $customerId = optional($row->customer)->id_customer;

                    if (! $customerId) {
                        return 'Rp. 0';
                    }

                    $sbum = Pemasukan::where('id_customer', $customerId)
                        ->whereNot('keterangan', 'GANTI NAMA')
                        ->where('id_jenis_pembayaran', 13)
                        ->sum('jumlah');

                    return 'Rp. ' . number_format($sbum, 0, ',', '.');
                })
                ->addColumn('sisa', function ($row) {
                    $customerId = optional($row->customer)->id;

                    if (! $customerId) {
                        return 'Rp. 0';
                    }

                    $totalTagihan = Piutang::where('id_customer', $customerId)->sum('nominal');
                    $jumlahBayar  = Pemasukan::where('id_customer', $customerId)
                        ->where('keterangan', 'NOT LIKE', 'Biaya ganti nama%')
                        ->sum('nominal');

                    $sisaBayar = $totalTagihan - $jumlahBayar;

                    return 'Rp. ' . number_format($sisaBayar, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $customerId = optional($row->customer)->id;

                    $detailUrl = $customerId
                        ? route('pembayaran.show', $customerId)
                        : null;

                    $btn = '<div class="d-flex justify-content-center">';

                    if (! empty($customerId)) {
                        $btn .= '<button class="mx-1 btn btn-primary btn-sm bayar-button"
                        data-id="' . e($customerId) . '"
                        data-toggle="modal"
                        data-target="#modalForm">
                        Bayar
                    </button>';
                    } else {
                        $btn .= '<button class="mx-1 btn btn-secondary btn-sm" disabled>Bayar</button>';
                    }

                    if (! empty($customerId)) {
                        $btn .= '<a href="' . $detailUrl . '" class="btn btn-success btn-sm">Detail</a>';
                    } else {
                        $btn .= '<button class="btn btn-secondary btn-sm" disabled>Detail</button>';
                    }

                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['action', 'lokasi'])
                ->make(true);
        }

        return view('admin.pembayaran.rekap', compact('pembayaran', 'lokasi'));
    }

    public function jatuhTempo(Request $request)
    {
        $lokasi = LokasiKavling::orderBy('nama_kavling', 'asc')->get();

        $kategoriTransaksiPemasukan = KategoriTransaksi::where('jenis_kategori', 'PEMASUKAN')
            ->where('id', 3)
            ->get();

        $metodeBayar = MetodeBayar::all();

        $config        = KonfigurasiAplikasi::first();
        $templatePesan = $config->pesan_jatuh_tempo ?? 'Mohon Bayar Jatuh Tempo Kavling';

        if ($request->ajax()) {

            $query = Customer::with([
                'kavling.lokasi',
                'piutangs',
            ])->where('stt_arsip', 0)->whereHas('kavling');

            if ($request->lokasi_id) {
                $query->whereHas('kavling', function ($q) use ($request) {
                    $q->where('id_lokasi', $request->lokasi_id);
                });
            }

            $data = $query->orderBy('nama_lengkap', 'asc')->get();

            $customerData = [];
            foreach ($data as $cust) {
                $totalTagihan = $cust->piutangs->sum('nominal');
                $totalBayar   = $cust->piutangs->sum('terbayar'); // sudah dibayar
                $pencairan    = $cust->piutangs->where('id_kategori_transaksi', 4)->sum('nominal');
                $sisa         = $totalTagihan - $totalBayar;

                $statusKeterlambatan = 'Lancar';

                if ($sisa <= 0 && $totalTagihan > 0) {
                    $statusKeterlambatan = 'Lunas';
                } elseif ($cust->inhouse_jatuh_tempo) {
                    $jatuhTempo = Carbon::parse($cust->inhouse_jatuh_tempo)->startOfDay();
                    if (Carbon::now()->startOfDay()->gt($jatuhTempo) && $sisa > 0) {
                        $diff                = (int) $jatuhTempo->diffInDays(Carbon::now()->startOfDay());
                        $statusKeterlambatan = "Telat: $diff Hari";
                    }
                }

                $customerData[$cust->id] = [
                    'pembayaran'    => $totalBayar,
                    'tagihan'       => $totalTagihan,
                    'pencairan'     => $pencairan,
                    'sisa'          => $sisa,
                    'keterlambatan' => $statusKeterlambatan,
                ];
            }

            if ($request->filled('filter')) {
                $data = $data->filter(function ($row) use ($customerData, $request) {
                    $keterlambatan = $customerData[$row->id]['keterlambatan'] ?? '';

                    switch ($request->filter) {
                        case 'telat':
                            return strpos($keterlambatan, 'Telat') !== false;
                        case 'lancar':
                            return strpos($keterlambatan, 'Lancar') !== false;
                        case 'lunas':
                            return strpos($keterlambatan, 'Lunas') !== false;
                        default:
                            return true;
                    }
                });
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('customer', function ($row) {
                    return $row->nama_lengkap ?? '-';
                })
                ->addColumn('tgl_jatuh_tempo', function ($row) {
                    if (! $row->inhouse_jatuh_tempo) {
                        return '-';
                    }

                    return Carbon::parse($row->inhouse_jatuh_tempo)
                        ->locale('id')
                        ->translatedFormat('j F');
                })
                ->addColumn('lokasi', function ($row) use ($request) {

                    $kavling = $row->kavling;

                    if ($request->lokasi_id) {
                        $kavling = $kavling->where('id_lokasi', $request->lokasi_id);
                    }

                    if ($kavling->isEmpty()) {
                        return '-';
                    }

                    $grouped = $kavling->groupBy(function ($item) {
                        return $item->lokasi->nama_kavling ?? 'Lainnya';
                    });

                    $output = '';

                    foreach ($grouped as $namaLokasi => $items) {

                        $output .= '<div class="mb-1"><strong class="text-primary">' . $namaLokasi . '</strong></div>';

                        $badges = $items->map(function ($item) {
                            return '<span class="mb-1 badge bg-info me-1">' . $item->kode_kavling . '</span>';
                        })->implode(' ');

                        $output .= '<div class="mb-2">' . $badges . '</div>';
                    }

                    return $output;
                })
                ->editColumn('hrg_jual', function ($row) use ($customerData) {
                    $totalHarga = $customerData[$row->id]['tagihan'] ?? 0;
                    return 'Rp. ' . number_format($totalHarga, 0, ',', '.');
                })
                ->addColumn('pembayaran', function ($row) use ($customerData) {
                    $val = $customerData[$row->id]['pembayaran'] ?? 0;
                    return 'Rp. ' . number_format($val, 0, ',', '.');
                })
                ->addColumn('sisa', function ($row) use ($customerData) {
                    $sisa = $customerData[$row->id]['sisa'] ?? 0;
                    if ($sisa <= 0) {
                        return '<span class="badge bg-success">Lunas</span>';
                    }
                    return 'Rp. ' . number_format($sisa, 0, ',', '.');
                })
                ->addColumn('keterlambatan', function ($row) use ($customerData) {
                    $status = $customerData[$row->id]['keterlambatan'] ?? '-';
                    if (strpos($status, 'Telat') !== false) {
                        return '<span class="badge bg-danger">' . $status . '</span>';
                    }
                    if ($status == 'Lunas') {
                        return '<span class="badge bg-success">Lunas</span>';
                    }
                    return '<span class="badge bg-info">Lancar</span>';
                })
                ->addColumn('action', function ($row) use ($customerData, $templatePesan) {

                    $detailUrl = route('pembayaran.detail', $row->id);

                    $status = $customerData[$row->id]['keterlambatan'] ?? '';

                    $waButton = '';

                    if (strpos($status, 'Telat') !== false && $row->no_wa) {

                        $noWa = preg_replace('/[^0-9]/', '', $row->no_wa);

                        if (substr($noWa, 0, 1) == '0') {
                            $noWa = '62' . substr($noWa, 1);
                        }

                        $jumlahTagihan = $customerData[$row->id]['tagihan'] ?? 0;

                        $pesan = $templatePesan;

                        $pesan = str_replace('[[nama_customer]]', $row->nama_lengkap, $pesan);
                        $pesan = str_replace('[[jumlah_bulan]]', $row->jumlah_bulan_x ?? 0, $pesan);
                        $pesan = str_replace(
                            '[[jumlah_tagihan]]',
                            'Rp. ' . number_format($jumlahTagihan, 0, ',', '.'),
                            $pesan
                        );

                        $message = urlencode($pesan);

                        $waUrl = 'https://wa.me/' . $noWa . '?text=' . $message;

                        $waButton = '<a href="' . $waUrl . '" target="_blank" class="btn btn-success btn-sm me-1">
                            <i class="fab fa-whatsapp"></i>
                            </a>';
                    }

                    return '
            <div class="d-flex justify-content-center">
                ' . $waButton . '
                <button class="btn btn-primary btn-sm edit-button me-1" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modalTempo">Edit</button>
                <button class="btn btn-danger btn-sm bayar-button me-1" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modalForm">Bayar</button>
                <a href="' . $detailUrl . '" class="btn btn-success btn-sm">Detail</a>
            </div>
            ';
                })
                ->rawColumns(['action', 'sisa', 'lokasi', 'keterlambatan'])
                ->make(true);
        }

        return view('admin.pembayaran.jatuhTempo', compact('lokasi', 'kategoriTransaksiPemasukan', 'metodeBayar'));
    }

    public function editJatuhTempo(Request $request, $id)
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'Customer tidak ditemukan'], 404);
        }

        DB::BeginTransaction();

        try {
            $request->merge([
                'inhouse_perbulan' => str_replace('.', '', $request->inhouse_perbulan),
            ]);

            $validator = Validator::make($request->all(), [
                'inhouse_perbulan'    => 'required',
                'inhouse_tenor'       => 'required',
                'inhouse_jatuh_tempo' => 'required|date',
            ], [
                'inhouse_perbulan.required'    => 'Inhouse perbulan harus diisi.',
                'inhouse_tenor.required'       => 'Inhouse tenor harus diisi.',
                'inhouse_jatuh_tempo.required' => 'Inhouse jatuh tempo harus diisi.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            $customer->update([
                'inhouse_perbulan'    => $request->inhouse_perbulan,
                'inhouse_tenor'       => $request->inhouse_tenor,
                'inhouse_jatuh_tempo' => $request->inhouse_jatuh_tempo,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Data customer berhasil diubah'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat mengubah data customer: ' . $e->getMessage()], 500);
        }
    }

    private function precalculateCustomerData($data)
    {
        $customerData = [];
        $hariIni      = Carbon::today();

        foreach ($data as $row) {
            $customer = $row->customer;
            if (! $customer) {
                continue;
            }

            $customerId = $customer->id_customer;

            // Hitung total tagihan
            $totalTagihan = $customer->tagihan->sum('jumlah_tagihan');

            // Hitung pembayaran
            $pembayaranValid = $customer->pembayaran->whereNotIn('id_jenis_pembayaran', [3, 10, 11, 12, 13])
                ->where('keterangan', '!=', 'GANTI NAMA');

            $jumlahBayar = $customer->pembayaran->where('keterangan', '!=', 'GANTI NAMA')->sum('jumlah');
            $pembayaran  = $pembayaranValid->sum('jumlah');
            $pencairan   = $customer->pembayaran->where('id_jenis_pembayaran', 3)
                ->where('keterangan', '!=', 'GANTI NAMA')
                ->sum('jumlah');
            $sbum = $customer->pembayaran->where('id_jenis_pembayaran', 13)
                ->where('keterangan', '!=', 'GANTI NAMA')
                ->sum('jumlah');

            $sisaBayar = $totalTagihan - $jumlahBayar;

            // Hitung keterlambatan
            $keterlambatan = $this->calculateKeterlambatan($customer, $sisaBayar, $hariIni);

            $customerData[$customerId] = [
                'pembayaran'    => $pembayaran,
                'pencairan'     => $pencairan,
                'sbum'          => $sbum,
                'sisa'          => $sisaBayar,
                'keterlambatan' => $keterlambatan,
            ];
        }

        return $customerData;
    }

    private function calculateKeterlambatan($customer, $sisaBayar, $hariIni)
    {
        Carbon::setLocale('id');

        if ($sisaBayar <= 0) {
            return '<span class="badge badge-success">Lunas</span>';
        }

        // Validasi data inhouse
        if (
            is_null($customer->inhouse_jatuh_tempo) ||
            is_null($customer->inhouse_perbulan) ||
            is_null($customer->inhouse_tenor) ||
            $customer->inhouse_jatuh_tempo === '0000-00-00' ||
            $customer->inhouse_jatuh_tempo === '' ||
            trim($customer->inhouse_jatuh_tempo) === '' ||
            $customer->inhouse_perbulan <= 0 ||
            $customer->inhouse_tenor <= 0
        ) {
            return '<span class="badge badge-secondary">Belum di set</span>';
        }

        // Validasi format tanggal
        try {
            $jatuhTempoAwal = Carbon::parse($customer->inhouse_jatuh_tempo)->startOfDay();
            if ($jatuhTempoAwal->year < 1900) {
                return '<span class="badge badge-secondary">Tanggal tidak valid</span>';
            }
        } catch (\Exception $e) {
            return '<span class="badge badge-secondary">Format tanggal salah</span>';
        }

        $hariIni = $hariIni->copy()->startOfDay();

        // Ambil pembayaran valid dari relasi yang sudah di-load
        $pembayaranList = $customer->pembayaran
            ->whereNotIn('keterangan', ['GANTI NAMA', 'BOOKING FEE'])
            ->whereNotIn('id_jenis_pembayaran', [3, 10, 13])
            ->filter(function ($p) use ($jatuhTempoAwal) {
                return Carbon::parse($p->tanggal)->gte($jatuhTempoAwal);
            })
            ->sortBy('tanggal');

        $totalPembayaran = $pembayaranList->sum('jumlah');

        $angsuranPerBulan = (float) $customer->inhouse_perbulan;
        $tenor            = (int) $customer->inhouse_tenor;

        if ($tenor > 60) {
            $tenor = 60;
        }

        // Generate jadwal jatuh tempo
        $jadwalJatuhTempo = [];
        for ($i = 0; $i < $tenor; $i++) {
            $jadwalJatuhTempo[] = [
                'bulan_ke' => $i + 1,
                'tanggal'  => $jatuhTempoAwal->copy()->addMonths($i)->startOfDay(),
                'nominal'  => $angsuranPerBulan,
                'terbayar' => 0,
                'sisa'     => $angsuranPerBulan,
            ];
        }

        // Tutup hutang FIFO
        $sisaPembayaran = $totalPembayaran;
        foreach ($jadwalJatuhTempo as &$jadwal) {
            if ($sisaPembayaran <= 0) {
                break;
            }

            if ($sisaPembayaran >= $jadwal['nominal']) {
                $jadwal['terbayar']  = $jadwal['nominal'];
                $jadwal['sisa']      = 0;
                $sisaPembayaran     -= $jadwal['nominal'];
            } else {
                $jadwal['terbayar'] = $sisaPembayaran;
                $jadwal['sisa']     = $jadwal['nominal'] - $sisaPembayaran;
                $sisaPembayaran     = 0;
            }
        }

        // Hitung keterlambatan
        $jumlahTelat       = 0;
        $totalNominalTelat = 0;

        foreach ($jadwalJatuhTempo as $jadwal) {
            if ($jadwal['sisa'] > 0 && $hariIni->gte($jadwal['tanggal'])) {
                $jumlahTelat++;
                $totalNominalTelat += $jadwal['sisa'];
            }
        }

        // Format tampilan tanggal jatuh tempo dan tenor
        $infoInhouse = '
        <div class="mt-1 small text-muted">
            <strong>Jatuh Tempo Awal:</strong> ' . $jatuhTempoAwal->translatedFormat('d F Y') . '<br>
            <strong>Tenor:</strong> ' . $tenor . ' bulan
        </div>
    ';

        if ($jumlahTelat > 0) {
            return '
            <div class="text-danger">
                <strong>Telat: ' . $jumlahTelat . 'x</strong><br>
                <small>Rp. ' . number_format($totalNominalTelat, 0, ',', '.') . '</small>
            </div>
            ' . $infoInhouse;
        } else {
            return '
            <div class="text-success">
                <span class="badge badge-success">Lancar</span>
            </div>
            ' . $infoInhouse;
        }
    }
}
