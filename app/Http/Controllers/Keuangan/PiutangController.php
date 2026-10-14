<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Bank;
use App\Models\LokasiKavling;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\Piutang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use TCPDF;
use Yajra\DataTables\Facades\DataTables;

class PiutangController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = Piutang::orderBy('id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal_piutang', function ($row) {
                    return Carbon::parse($row->tanggal_piutang)->translatedFormat('j F Y');
                })
                ->editColumn('nominal', function ($row) {
                    return '
                    <div class="d-flex justify-content-between harga-format w-100">
                        <span>Rp.</span>
                        <span>'.number_format($row->nominal, 0, ',', '.').'</span>
                    </div>';
                })
                ->editColumn('status', function ($row) {
                    switch ($row->status) {
                        case 1:
                            return '<span class="badge bg-danger">Belum Lunas</span>';
                        case 2:
                            return '<span class="badge bg-success">Sudah Lunas</span>';
                        default:
                            return '<span class="badge bg-warning">Status Tidak Dikenal</span>';
                    }
                })
                ->addColumn('lampiran', function ($row) {
                    if ($row->lampiran) {
                        return '<button class="btn btn-sm btn-success show-lampiran" data-file="'.e($row->lampiran).'" data-toggle="modal" data-target="#modallampiran">Lihat</button>';
                    }

                    return '-';
                })
                ->addColumn('tgl_pelunasan', function ($row) {
                    return $row->tgl_pelunasan
                    ? Carbon::parse($row->tgl_pelunasan)->translatedFormat('j F Y')
                    : '';
                })
                ->filterColumn('tanggal_piutang', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->WhereDate('tanggal_piutang', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function ($row) use ($permissions) {
                    $editUrl = route('piutang.edit', $row->id);
                    $detailUrl = route('piutang.show', $row->id);
                    $deleteUrl = route('piutang.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';
                    if ($row->id_customer != 0) {
                        $btn .= '<button class="btn btn-primary btn-sm mx-1 detail-button"
                                data-id="'.e($row->id).'"
                                data-url="'.e($detailUrl).'">Detail</button>';
                    } else {
                        if ($permissions['edit']) {
                            $btn .= '<button class="btn btn-primary btn-sm mx-1 edit-button"
                                data-id="'.e($row->id).'"
                                data-url="'.e($editUrl).'">Edit</button>';
                        }
                    }

                    if ($permissions['hapus']) {
                        $btn .= '<form action="'.e($deleteUrl).'" method="POST" style="display:inline;">
                    '.csrf_field().method_field('DELETE').'
                    <button type="submit" class="delete-button btn btn-danger btn-sm mx-1">
                        Hapus
                    </button>
                    </form>';
                    }
                    $btn .= '</div>';

                    return $btn;
                })

                ->rawColumns(['action', 'tanggal_piutang', 'nominal', 'status', 'tgl_bayar_hutang'])
                ->make(true);
        }

        $bankList = Bank::all();

        return view('admin.keuangan.piutang.index', compact('permissions', 'bankList'));
    }

    public function edit($id)
    {
        $list = Piutang::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $list,
        ]);
    }

    public function show($id)
    {
        $list = Piutang::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $list,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_piutang' => 'required|date',
            'nominal' => 'required',
            'id_bank' => 'required',
            'deskripsi' => 'required',
            'lampiran' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'tanggal_piutang.required' => 'Tanggal piutang wajib diisi.',
            'tanggal_piutang.date' => 'Tanggal piutang harus berupa tanggal.',
            'nominal.required' => 'Nominal wajib diisi.',
            'id_bank.required' => 'Bank wajib diisi.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'lampiran.required' => 'Lampiran wajib diisi.',
            'lampiran.file' => 'Lampiran harus berupa file.',
            'lampiran.mimes' => 'Format lampiran harus jpg, jpeg, png, atau pdf.',
            'lampiran.max' => 'Ukuran file maksimal 2MB.',
        ]);

        $db = [
            'id_bank' => $request->id_bank,
            'tanggal_piutang' => $request->tanggal_piutang,
            'deskripsi' => $request->deskripsi,
            'nominal' => str_replace('.', '', $request->nominal),
            'lampiran' => '',
            'status' => 1,
            'terbayar' => 0,
            'sisa_bayar' => str_replace('.', '', $request->nominal),
            'tgl_pelunasan' => null,
        ];

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $ext = $file->getClientOriginalExtension();
            $filename = Str::random(25).'.'.$ext;

            $file->move(public_path('assets/keuangan/pengeluaran/'), $filename);

            $db['lampiran'] = $filename;
        }

        $piutang = Piutang::create($db);

        Pengeluaran::create([
            'id_bank' => $request->id_bank,
            'id_piutang' => $piutang->id,
            'tanggal' => $request->tanggal_piutang,
            'nominal' => str_replace('.', '', $request->nominal),
            'lampiran' => $filename,
            'id_kategori_transaksi' => 11,
            'keterangan' => $request->deskripsi,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function update(Request $request, $id)
    {
        $data = Piutang::findOrFail($id);

        $rules = [
            'tanggal_piutang' => 'required|date',
            'nominal' => 'required',
            'id_bank' => 'required',
            'deskripsi' => 'required',
        ];

        if (empty($data->lampiran)) {
            $rules['lampiran'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
        } elseif ($request->hasFile('lampiran')) {
            $rules['lampiran'] = 'file|mimes:jpg,jpeg,png,pdf|max:2048';
        }

        $request->validate($rules, [
            'tanggal_piutang.required' => 'Tanggal Piutang wajib diisi.',
            'tanggal_piutang.date' => 'Tanggal Piutang harus berupa tanggal.',
            'nominal.required' => 'Nominal wajib diisi.',
            'id_bank.required' => 'Bank wajib diisi.',
            'lampiran.required' => 'Lampiran wajib diisi.',
            'lampiran.file' => 'Lampiran harus berupa file.',
            'lampiran.mimes' => 'Format lampiran harus jpg, jpeg, png, atau pdf.',
            'lampiran.max' => 'Ukuran file maksimal 2MB.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
        ]);

        $db = [
            'tanggal_piutang' => $request->tanggal_piutang,
            'deskripsi' => $request->deskripsi,
            'id_bank' => $request->id_bank,
            'nominal' => str_replace('.', '', $request->nominal),
            'sisa_bayar' => str_replace('.', '', $request->nominal),
        ];

        $filename = $data->lampiran;

        if ($request->hasFile('lampiran')) {
            if (! empty($data->lampiran) && file_exists(public_path('assets/keuangan/pengeluaran/'.$data->lampiran))) {
                unlink(public_path('assets/keuangan/pengeluaran/'.$data->lampiran));
            }

            $file = $request->file('lampiran');
            $ext = $file->getClientOriginalExtension();
            $filename = Str::random(25).'.'.$ext;
            $file->move(public_path('assets/keuangan/pengeluaran/'), $filename);

            $db['lampiran'] = $filename;
        }

        $data->update($db);

        $pengeluaran = Pengeluaran::where('id_piutang', $id)->first();
        $pengeluaran->update([
            'tanggal' => $request->tanggal_hutang,
            'id_bank' => $request->id_bank,
            'nominal' => str_replace('.', '', $request->nominal),
            'lampiran' => $filename,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function destroy($id)
    {
        $data = Piutang::findOrFail($id);
        if (! empty($data->lampiran) && file_exists(public_path('assets/keuangan/pengeluaran/'.$data->lampiran))) {
            unlink(public_path('assets/keuangan/pengeluaran/'.$data->lampiran));
        }

        $pengeluaran = Pengeluaran::where('id_piutang', $id)->first();

        if ($pengeluaran) {
            if (! empty($pengeluaran->lampiran) && file_exists(public_path('assets/keuangan/pengeluaran/'.$pengeluaran->lampiran))) {
                unlink(public_path('assets/keuangan/pengeluaran/'.$pengeluaran->lampiran));
            }
            $pengeluaran->delete();
        }
        $pemasukan = Pemasukan::where('id_piutang', $id)->first();

        if ($pemasukan) {
            if (! empty($pemasukan->lampiran) && file_exists(public_path('assets/keuangan/pemasukan/'.$pemasukan->lampiran))) {
                unlink(public_path('assets/keuangan/pemasukan/'.$pemasukan->lampiran));
            }
            $pemasukan->delete();
        }

        $data->delete();

        return response()->json(['status' => 'success']);
    }

    public function getSisaBayar($id)
    {
        $hutang = Piutang::find($id);

        if (! $hutang) {
            return response()->json(['sisa_bayar' => 0], 404);
        }

        return response()->json(['sisa_bayar' => $hutang->sisa_bayar]);
    }

    public function rekap()
    {
        $monthList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $lokasiList = LokasiKavling::all();
        $tahunList = DB::table('piutang')->selectRaw('YEAR(tanggal_piutang) as tahun')->pluck('tahun')->unique()->toArray();
        $oldYear = ! empty($tahunList) ? min($tahunList) : now()->year;

        return view('admin.keuangan.piutang.rekap', compact('monthList', 'lokasiList', 'oldYear'));
    }

    public function filter(Request $request)
    {
        try {
            Carbon::setLocale('id');

            $tahun = $request->tahun;
            $bulan = $request->bulan;
            $lokasi = $request->lokasi;
            $status = $request->status;

            $query = DB::table('piutang')
                ->leftJoin('customer', 'piutang.id_customer', '=', 'customer.id')
                ->leftJoin('lokasi_kavling', 'customer.id_lokasi', '=', 'lokasi_kavling.id')
                ->whereYear('piutang.tanggal_piutang', $tahun)
                ->when($bulan != 0, fn ($q) => $q->whereMonth('piutang.tanggal_piutang', $bulan))
                ->when($status != 0, fn ($q) => $q->where('piutang.status', $status))
                ->when($lokasi === 'all_lokasi', function ($q) {
                    $q->whereNotNull('piutang.id_customer')
                        ->where('piutang.id_customer', '!=', 0);
                })
                ->when($lokasi === 'other', function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('piutang.id_customer')
                            ->orWhere('piutang.id_customer', 0);
                    });
                })
                ->when(
                    $lokasi !== 'all_lokasi' && $lokasi !== 'other' && $lokasi != 0,
                    fn ($q) => $q->where('customer.id_lokasi', $lokasi)
                )
                ->select(
                    'piutang.id',
                    'piutang.tanggal_piutang',
                    'piutang.deskripsi',
                    'piutang.nominal',
                    'piutang.status',
                    'piutang.tgl_pelunasan',
                    'lokasi_kavling.nama_kavling'
                )
                ->orderByDesc('piutang.tanggal_piutang');

            $results = $query->get();

            $totalPiutang = $results->sum('nominal');
            $totalLunas = $results->where('status', 2)->sum('nominal');
            $totalBelumLunas = $results->where('status', 1)->sum('nominal');

            if ($request->ajax()) {
                return DataTables::of($results)
                    ->addIndexColumn()
                    ->addColumn('tanggal_piutang', fn ($row) => Carbon::parse($row->tanggal_piutang)->translatedFormat('d F Y'))
                    ->addColumn('nominal_format', function ($row) {
                        return '
                        <div class="d-flex justify-content-between harga-format w-100">
                            <span>Rp.</span>
                            <span>'.number_format($row->nominal, 0, ',', '.').'</span>
                        </div>';
                    })
                    ->addColumn('status_badge', function ($row) {
                        switch ($row->status) {
                            case 1:
                                return '<span class="badge bg-danger">Belum Lunas</span>';
                            case 2:
                                return '<span class="badge bg-success">Sudah Lunas</span>';
                            default:
                                return '<span class="badge bg-warning">Status Tidak Dikenal</span>';
                        }
                    })
                    ->addColumn('tgl_pelunasan', fn ($row) => $row->tgl_pelunasan
                        ? Carbon::parse($row->tgl_pelunasan)->translatedFormat('d F Y')
                        : '-')
                    ->addColumn('lokasi', fn ($row) => $row->nama_kavling ?? 'Piutang Manajemen')
                    ->with([
                        'total_piutang' => $totalPiutang,
                        'total_lunas' => $totalLunas,
                        'total_belum_lunas' => $totalBelumLunas,
                    ])
                    ->rawColumns(['nominal_format', 'status_badge'])
                    ->make(true);
            }

            return response()->json(['message' => 'Invalid request'], 400);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            Carbon::setLocale('id');

            $request->validate([
                'tahun' => 'required|numeric',
                'bulan' => 'required|numeric',
                'lokasi' => 'required',
                'status' => 'required|numeric',
            ]);

            $tahun = $request->tahun;
            $bulan = $request->bulan;
            $lokasi = $request->lokasi;
            $status = $request->status;

            $lokasiNama = $lokasi == 'all_lokasi' ? 'Semua Lokasi' : ($lokasi == 'other' ? 'Piutang Manajemen' : (LokasiKavling::find($lokasi)?->nama_kavling ?? 'Tidak Diketahui'));
            $statusNama = $status == 0 ? 'Semua Status' : ($status == 1 ? 'Belum Lunas' : 'Sudah Lunas');

            $query = DB::table('piutang')
                ->leftJoin('customer', 'piutang.id_customer', '=', 'customer.id')
                ->leftJoin('lokasi_kavling', 'customer.id_lokasi', '=', 'lokasi_kavling.id')
                ->whereYear('piutang.tanggal_piutang', $tahun)
                ->when($bulan != 0, fn ($q) => $q->whereMonth('piutang.tanggal_piutang', $bulan))
                ->when($status != 0, fn ($q) => $q->where('piutang.status', $status))
                ->when($lokasi === 'all_lokasi', function ($q) {
                    $q->whereNotNull('piutang.id_customer')
                        ->where('piutang.id_customer', '!=', 0);
                })
                ->when($lokasi === 'other', function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('piutang.id_customer')
                            ->orWhere('piutang.id_customer', 0);
                    });
                })
                ->when(
                    $lokasi !== 'all_lokasi' && $lokasi !== 'other' && $lokasi != 0,
                    fn ($q) => $q->where('customer.id_lokasi', $lokasi)
                )
                ->select(
                    'piutang.id',
                    'piutang.tanggal_piutang',
                    'piutang.deskripsi',
                    'piutang.nominal',
                    'piutang.status',
                    'piutang.tgl_pelunasan',
                    'lokasi_kavling.nama_kavling'
                )
                ->orderByDesc('piutang.tanggal_piutang');

            $data = $query->get();

            $totalPiutang = $data->sum('nominal');
            $totalLunas = $data->where('status', 2)->sum('nominal');
            $totalBelumLunas = $data->where('status', 1)->sum('nominal');

            $periode = $bulan == 0
                ? 'Tahun '.$tahun
                : Carbon::createFromDate($tahun, $bulan)->translatedFormat('F Y');

            $pdf = new TCPDF;
            $pdf->SetPrintHeader(false);
            $pdf->SetPrintFooter(false);

            // 1. PENTING: Set Auto Page Break agar otomatis pindah halaman
            // Angka 15 adalah margin bawah (mm) sebelum pindah halaman
            $pdf->SetAutoPageBreak(true, 15);

            $pdf->SetMargins(15, 15, 15);
            $pdf->AddPage('P', 'A4');
            $pdf->SetFont('helvetica', '', 9);

            // Header Laporan
            $html = '
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td align="center" style="font-size: 16px; font-weight: bold;">LAPORAN REKAP PIUTANG</td>
            </tr>
            <tr>
                <td align="center" style="font-size: 10px; color: #555;">Generated by System</td>
            </tr>
        </table>
        <hr style="height: 1px; border: 0; border-top: 1px solid #333;">
        <br>';

            // Info Filter
            $html .= '
        <table border="0" cellpadding="3" cellspacing="0" width="100%" style="font-size: 10px;">
            <tr>
                <td width="15%" style="font-weight: bold;">Periode</td>
                <td width="5%">:</td>
                <td width="80%">'.$periode.'</td>
            </tr>
            <tr>
                <td width="15%" style="font-weight: bold;">Lokasi</td>
                <td width="5%">:</td>
                <td width="80%">'.$lokasiNama.'</td>
            </tr>
            <tr>
                <td width="15%" style="font-weight: bold;">Status</td>
                <td width="5%">:</td>
                <td width="80%">'.$statusNama.'</td>
            </tr>
        </table>
        <br><br>';

            // Tabel Data
            // TCPDF otomatis mengulang <thead> jika tabel pindah halaman
            $html .= '
        <table border="0" cellpadding="6" cellspacing="0" width="100%" style="border-collapse: collapse;">
            <thead>
                <tr style="background-color: #333333; color: #ffffff; font-weight: bold;">
                    <th width="5%" align="center" style="border: 1px solid #333;">No</th>
                    <th width="15%" align="center" style="border: 1px solid #333;">Tanggal</th>
                    <th width="35%" align="left" style="border: 1px solid #333;">Deskripsi</th>
                    <th width="15%" align="right" style="border: 1px solid #333;">Nominal</th>
                    <th width="15%" align="center" style="border: 1px solid #333;">Status</th>
                    <th width="15%" align="center" style="border: 1px solid #333;">Tgl Lunas</th>
                </tr>
            </thead>
            <tbody>';

            $no = 1;
            foreach ($data as $row) {
                if ($row->status == 1) {
                    $statusText = '<span style="color: #d9534f;">Belum Lunas</span>';
                } elseif ($row->status == 2) {
                    $statusText = '<span style="color: #28a745;">Sudah Lunas</span>';
                } else {
                    $statusText = '-';
                }

                $tglPelunasan = $row->tgl_pelunasan ? Carbon::parse($row->tgl_pelunasan)->translatedFormat('d/m/Y') : '-';
                $tglPiutang = Carbon::parse($row->tanggal_piutang)->translatedFormat('d/m/Y');
                $bgColor = ($no % 2 == 0) ? '#f9f9f9' : '#ffffff';

                // 2. PENTING: Tambahkan nobr="true" agar baris tidak terpotong di tengah
                $html .= '
                <tr nobr="true" style="background-color: '.$bgColor.';">
                    <td align="center" style="border-bottom: 1px solid #ddd;">'.$no++.'</td>
                    <td align="center" style="border-bottom: 1px solid #ddd;">'.$tglPiutang.'</td>
                    <td align="left" style="border-bottom: 1px solid #ddd;">'.$row->deskripsi.'</td>
                    <td align="right" style="border-bottom: 1px solid #ddd;">'.number_format($row->nominal, 0, ',', '.').'</td>
                    <td align="center" style="border-bottom: 1px solid #ddd;">'.$statusText.'</td>
                    <td align="center" style="border-bottom: 1px solid #ddd;">'.$tglPelunasan.'</td>
                </tr>';
            }

            if ($no === 1) {
                $html .= '<tr><td colspan="6" align="center" style="padding: 20px; font-style: italic;">Tidak ada data ditemukan.</td></tr>';
            }

            // Bagian Total (Gunakan nobr="true" juga agar blok total tidak terpisah)

            $html .= '
            <tr><td colspan="6" style="border-top: 1px solid #333;"></td></tr>
            <tr nobr="true" style="font-weight: bold; background-color: #e9ecef;">
                <td colspan="2" align="center">Total Piutang: Rp '.number_format($totalPiutang, 0, ',', '.').'</td>
                <td colspan="2" align="center" style="color: #28a745;">Lunas: Rp '.number_format($totalLunas, 0, ',', '.').'</td>
                <td colspan="2" align="center" style="color: #d9534f;">Belum Lunas: Rp '.number_format($totalBelumLunas, 0, ',', '.').'</td>
            </tr>';

            $html .= '</tbody></table>';

            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('laporan_rekap_piutang.pdf');

        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            return back()->with('error', 'Gagal membuat PDF: '.$e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            Carbon::setLocale('id');

            $request->validate([
                'tahun' => 'required|integer',
                'bulan' => 'required|integer',
                'lokasi' => 'required',
                'status' => 'required|integer',
            ]);

            $tahun = $request->tahun;
            $bulan = $request->bulan;
            $lokasi = $request->lokasi;
            $status = $request->status;

            $lokasiNama = $lokasi == 'all_lokasi' ? 'Semua Lokasi' : ($lokasi == 'other' ? 'Piutang Manajemen' : (LokasiKavling::find($lokasi)?->nama_kavling ?? 'Tidak Diketahui'));
            $statusNama = $status == 0 ? 'Semua Status' : ($status == 1 ? 'Belum Lunas' : 'Sudah Lunas');

            $query = DB::table('piutang')
                ->leftJoin('customer', 'piutang.id_customer', '=', 'customer.id')
                ->leftJoin('lokasi_kavling', 'customer.id_lokasi', '=', 'lokasi_kavling.id')
                ->whereYear('piutang.tanggal_piutang', $tahun)
                ->when($bulan != 0, fn ($q) => $q->whereMonth('piutang.tanggal_piutang', $bulan))
                ->when($status != 0, fn ($q) => $q->where('piutang.status', $status))
                ->when($lokasi === 'all_lokasi', function ($q) {
                    $q->whereNotNull('piutang.id_customer')
                        ->where('piutang.id_customer', '!=', 0);
                })
                ->when($lokasi === 'other', function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('piutang.id_customer')
                            ->orWhere('piutang.id_customer', 0);
                    });
                })
                ->when(
                    $lokasi !== 'all_lokasi' && $lokasi !== 'other' && $lokasi != 0,
                    fn ($q) => $q->where('customer.id_lokasi', $lokasi)
                )
                ->select(
                    'piutang.id',
                    'piutang.tanggal_piutang',
                    'piutang.deskripsi',
                    'piutang.nominal',
                    'piutang.status',
                    'piutang.tgl_pelunasan',
                    'lokasi_kavling.nama_kavling'
                )
                ->orderByDesc('piutang.tanggal_piutang');

            $results = $query->get();

            $periode = $bulan == 0
                ? 'Tahun '.$tahun
                : Carbon::createFromDate($tahun, $bulan)->translatedFormat('F Y');

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            // --- 1. Header Judul Laporan (Styling Pro) ---
            $sheet->setCellValue('A1', 'LAPORAN REKAP PIUTANG');
            $sheet->setCellValue('A2', 'Periode : '.$periode);
            $sheet->setCellValue('A3', 'Lokasi   : '.$lokasiNama);
            $sheet->setCellValue('A4', 'Status   : '.$statusNama);

            $sheet->mergeCells('A1:F1');
            $sheet->mergeCells('A2:F2');
            $sheet->mergeCells('A3:F3');
            $sheet->mergeCells('A4:F4');

            // Style Judul Utama (A1)
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Style Metadata (A2-A4)
            $sheet->getStyle('A2:A4')->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'font' => ['color' => ['argb' => '555555']],
            ]);

            // --- 2. Header Tabel ---
            $headers = ['No', 'Tanggal', 'Deskripsi', 'Nominal', 'Status', 'Tgl Pelunasan'];
            $sheet->fromArray($headers, null, 'A6');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['argb' => Color::COLOR_WHITE]],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '333333'], // Dark Grey Background
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFF']],
                ],
            ];
            $sheet->getStyle('A6:F6')->applyFromArray($headerStyle);
            $sheet->getRowDimension(6)->setRowHeight(25); // Tinggi baris header

            // --- 3. Loop Data ---
            $row = 7;
            $no = 1;
            $totalPiutang = 0;
            $totalLunas = 0;
            $totalBelumLunas = 0;

            foreach ($results as $item) {
                $statusText = $item->status == 1 ? 'Belum Lunas' : ($item->status == 2 ? 'Sudah Lunas' : '-');
                $tglPelunasan = $item->tgl_pelunasan ? Carbon::parse($item->tgl_pelunasan)->translatedFormat('d F Y') : '-';

                $totalPiutang += $item->nominal;
                if ($item->status == 2) {
                    $totalLunas += $item->nominal;
                } elseif ($item->status == 1) {
                    $totalBelumLunas += $item->nominal;
                }

                $sheet->setCellValue("A$row", $no++);
                $sheet->setCellValue("B$row", Carbon::parse($item->tanggal_piutang)->translatedFormat('d F Y'));
                $sheet->setCellValue("C$row", $item->deskripsi);
                $sheet->setCellValueExplicit("D$row", $item->nominal, DataType::TYPE_NUMERIC);
                $sheet->setCellValue("E$row", $statusText);
                $sheet->setCellValue("F$row", $tglPelunasan);

                // Conditional Formatting untuk Status (Warna Teks)
                if ($item->status == 1) { // Belum Lunas - Merah
                    $sheet->getStyle("E$row")->getFont()->getColor()->setARGB(Color::COLOR_RED);
                } elseif ($item->status == 2) { // Sudah Lunas - Hijau
                    $sheet->getStyle("E$row")->getFont()->getColor()->setARGB('009933'); // Dark Green
                }

                // Zebra Striping (Warna baris selang-seling)
                if ($row % 2 == 0) {
                    $sheet->getStyle("A$row:F$row")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('F2F2F2'); // Light Grey
                }

                $row++;
            }

            // --- 4. Styling Total ---
            $sheet->setCellValue("A$row", 'Total Piutang: Rp '.number_format($totalPiutang, 0, ',', '.'));
            $sheet->setCellValue("C$row", 'Lunas: Rp '.number_format($totalLunas, 0, ',', '.'));
            $sheet->setCellValue("E$row", 'Belum Lunas: Rp '.number_format($totalBelumLunas, 0, ',', '.'));

            $sheet->mergeCells("A$row:B$row");
            $sheet->mergeCells("C$row:D$row");
            $sheet->mergeCells("E$row:F$row");

            $sheet->getStyle("A$row:F$row")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE699'], // Kuning lembut untuk total
                ],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_DOUBLE],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Kondisional warna text pada Total
            $sheet->getStyle("C$row")->getFont()->getColor()->setARGB('009933'); // Hijau untuk Lunas
            $sheet->getStyle("E$row")->getFont()->getColor()->setARGB(Color::COLOR_RED); // Merah untuk Belum Lunas

            // --- 5. Final Touch: Border & Alignment Global ---
            $lastRow = $row;
            $range = 'A6:F'.$lastRow;

            // Border luar tabel
            $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Alignment Kolom Tertentu
            $sheet->getStyle("A7:A$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
            $sheet->getStyle("B7:B$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tanggal
            $sheet->getStyle("E7:E$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status
            $sheet->getStyle("F7:F$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tgl Lunas

            // Format Angka Rupiah (Tanpa Rp di sel, hanya format excel)
            $sheet->getStyle('D7:D'.($lastRow - 1))
                ->getNumberFormat()
                ->setFormatCode('#,##0');

            // Auto Size Column
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'laporan_rekap_piutang_'.Carbon::now()->format('Y-m-d').'.xlsx';
            $writer = new Xlsx($spreadsheet);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"$filename\"");
            $writer->save('php://output');
            exit;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            return back()->with('error', 'Gagal membuat Excel: '.$e->getMessage());
        }
    }
}
