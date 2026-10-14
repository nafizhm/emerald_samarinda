<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\KavlingPeta;
use App\Models\KonfigurasiAplikasi;
use App\Models\KonfigurasiMedia;
use App\Models\ListPenjualan;
use App\Models\LokasiKavling;
use App\Models\Marketing;
use App\Models\Pemasukan;
use App\Models\PersyaratanLegal;
use App\Models\Piutang;
use App\Models\TransaksiKavling;
use App\Services\PurchaseSelection;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;
use TCPDF;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function arsip(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = Customer::with(['marketing', 'kavling.lokasi', 'progres'])
                ->where('stt_arsip', 1)
                ->orderBy('id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()

                ->editColumn('tgl_cancel', function ($row) {
                    $tgl = $row->tgl_cancel ? Carbon::parse($row->tgl_cancel)->translatedFormat('d F Y') : '-';
                    $kode = $row->kode_customer ? '<strong>' . e($row->kode_customer) . '</strong>' : '';
                    return "$tgl<br>$kode";
                })

                ->editColumn('id_marketing', function ($row) {
                    return $row->marketing->nama_marketing ?? '<span class="badge bg-danger">Non Marketing</span>';
                })

                ->editColumn('id_lokasi', function ($row) {
                    if ($row->kavling->count() == 0) {
                        return '-';
                    }

                    $output = [];
                    foreach ($row->kavling as $item) {
                        $output[] = '<strong>' . ($item->lokasi->nama_kavling ?? '-') . '</strong><br> ' . ($item->kode_kavling ?? '-');
                    }

                    return implode('<hr style="margin: 5px 0; border-top: 1px dashed #ccc;">', $output);
                })

                ->editColumn('id_status_progres', function ($row) {
                    $status = $row->progres->status_progres ?? '-';
                    $badgeColors = [
                        'BF'           => 'warning',
                        'WAWANCARA'    => 'secondary',
                        'SP3K'         => 'success',
                        'AKAD'         => 'info',
                        'SERAH TERIMA' => 'dark',
                    ];
                    $statusDisplay = array_key_exists($status, $badgeColors)
                        ? '<span class="badge bg-' . $badgeColors[$status] . '">' . $status . '</span>'
                        : $status;
                    return $statusDisplay;
                })

                ->editColumn('nama_lengkap', function ($row) {
                    $nama = '<strong>' . e($row->nama_lengkap) . '</strong>';
                    $wa   = e($row->no_wa ?: ($row->no_telp ?: '-'));
                    $ktp  = $row->no_ktp ? '<span class="badge bg-info">NIK: ' . e($row->no_ktp) . '</span>' : '';
                    return "$nama<br>$wa<br>$ktp";
                })

                ->addColumn('action', function ($row) use ($permissions): string {
                    $btn = '<div class="text-center">';
                    if ($permissions['hapus']) {
                        $btn .= '<button type="button" class="btn btn-success btn-sm restore-button" data-id="' . $row->id . '">
                            Kembalikan
                        </button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })

                ->rawColumns([
                    'tgl_cancel',
                    'nama_lengkap',
                    'id_marketing',
                    'id_lokasi',
                    'id_status_progres',
                    'alasan_cancel',
                    'action',
                ])

                ->filter(function ($row) use ($request) {
                    if ($request->has('search.value')) {
                        $searchValue = $request->input('search.value');
                        $row->where(function ($query) use ($searchValue) {
                            $query->where('nama_lengkap', 'like', "%{$searchValue}%")
                                ->orWhere('kode_customer', 'like', "%{$searchValue}%")
                                ->orWhere('no_telp', 'like', "%{$searchValue}%")
                                ->orWhere('no_wa', 'like', "%{$searchValue}%");
                        });
                    }
                })
                ->make(true);
        }

        return view('admin.customer.arsip.index', compact('permissions'));
    }

    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        Carbon::setLocale('id');

        if ($request->ajax()) {
            $data = Customer::with(['marketing', 'kavling.lokasi', 'progres'])
                ->addSelect([
                    'latest_tgl_terima' => DB::table('transaksi_kavling')
                        ->select('tgl_terima')
                        ->whereColumn('id_customer', 'customer.id')
                        ->orderBy('tgl_terima', 'desc')
                        ->limit(1),
                ])
                ->where('stt_arsip', 0)
                ->orderBy('latest_tgl_terima', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()

                ->editColumn('tgl_terima', function ($row) {
                    $firstKavling = $row->kavling->first(); // ambil yang pertama

                    $tglFinal = ($firstKavling && $firstKavling->pivot->tgl_terima)
                        ? Carbon::parse($firstKavling->pivot->tgl_terima)->translatedFormat('d F Y')
                        : '-';

                    $method = e($row->jenis_pembelian === 'Cash Keras' ? 'Cash' : ($row->jenis_pembelian ?: '-'));
                    return '<span class="customer-date">'.$tglFinal.'</span><small class="customer-purchase-method">'.$method.'</small>';
                })

                ->editColumn('id_marketing', function ($row) {
                    $namaMarketing = e($row->marketing->nama_marketing ?? 'Belum ditentukan');

                    return $namaMarketing;
                })

                ->editColumn('id_lokasi', function ($row) {
                    if ($row->kavling->count() == 0) {
                        return '-';
                    }

                    $output = [];
                    foreach ($row->kavling as $item) {
                        $namaLokasi  = e($item->lokasi->nama_kavling ?? '-');
                        $kodeKavling = e($item->kode_kavling ?? '-');

                        $output[] = '<strong>' . $namaLokasi . '</strong><br> ' . $kodeKavling;
                    }

                    return implode('<hr style="margin: 5px 0; border-top: 1px dashed #ccc;">', $output);
                })

                ->editColumn('id_status_progres', function ($row) {
                    $status      = e($row->progres->status_progres ?? '-');
                    $ketCashback = $row->progres->ket_cashback ?? '';

                    $badgeColors = [
                        'BF'           => 'warning',
                        'WAWANCARA'    => 'secondary',
                        'SP3K'         => 'success',
                        'AKAD'         => 'info',
                        'SERAH TERIMA' => 'dark',
                    ];

                    if (array_key_exists($status, $badgeColors)) {
                        $statusDisplay = '<span class="badge bg-' . $badgeColors[$status] . '">' . $status . '</span>';
                    } else {
                        $statusDisplay = $status;
                    }

                    $cashbackText = $ketCashback ? '<br><small>' . e($ketCashback) . '</small>' : '';

                    return $statusDisplay . $cashbackText;
                })

                ->editColumn('nama_lengkap', function ($row) {
                    return '<div class="customer-identity"><strong>'.e($row->nama_lengkap).'</strong><small>'.e($row->kode_customer ?: '-').'</small><span>'.e($row->no_wa ?: ($row->no_telp ?: '-')).'</span></div>';
                })

                ->addColumn('action', function ($row) use ($permissions): string {
                    $editUrl   = route('customer.edit', $row->id);
                    $btn       = '<div class="customer-list-actions">';
                    $btn .= '<a href="'.e(route('cetak.subsidi', $row->id)).'" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">Cetak akad</a>';

                    if ($permissions['edit']) {
                        $btn .= '<a href="' . e($editUrl) . '" class="btn btn-outline-primary btn-sm">
                        Edit
                        </a>';
                    }

                    if ($permissions['hapus']) {
                        $btn .= '<button type="button" class="btn btn-outline-danger btn-sm cancel-button" data-id="' . $row->id . '" data-nama="' . e($row->nama_lengkap) . '">
                            Batalkan
                        </button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })

                ->rawColumns([
                    'tgl_terima',
                    'nama_lengkap',
                    'id_marketing',
                    'id_lokasi',
                    'id_status_progres',
                    'action',
                ])

                ->filter(function ($row) use ($request) {
                    if ($request->has('search.value')) {
                        $searchValue = $request->input('search.value');

                        $row->where(function ($query) use ($searchValue) {
                            $query->where('nama_lengkap', 'like', "%{$searchValue}%")
                                ->orWhere('kode_customer', 'like', "%{$searchValue}%")
                                ->orWhere('no_telp', 'like', "%{$searchValue}%")
                                ->orWhere('no_wa', 'like', "%{$searchValue}%");
                        });
                    }
                })
                ->make(true);
        }

        $lokasi = LokasiKavling::select('id', 'nama_kavling')->orderBy('nama_kavling', 'ASC')->get();

        return view('admin.customer.customer.index', compact('permissions', 'lokasi'));
    }

    public function create()
    {
        $cust      = null;
        $marketing = Marketing::all();
        $progres   = ListPenjualan::all();
        $lokasi    = LokasiKavling::all();
        $bankList  = Bank::all();

        return view('admin.customer.customer.create', compact('cust', 'marketing', 'progres', 'lokasi', 'bankList'));
    }

    public function getKavling(Request $request, $idLokasi)
    {
        $customerId = $request->query('customer_id');

        $data = KavlingPeta::select('id', 'kode_kavling')
            ->where('id_lokasi', $idLokasi)
            ->where(function ($query) use ($customerId) {
                $query->whereDoesntHave('customer', function ($q) {
                    $q->where('stt_arsip', 0);
                });

                if ($customerId) {
                    $query->orWhereHas('customer', function ($q) use ($customerId) {
                        $q->where('customer.id', $customerId);
                    });
                }
            })
            ->get();

        return response()->json($data);
    }

    public function getHargaKavling($ids)
    {
        $idArray    = explode(',', $ids);
        $totalHarga = KavlingPeta::whereIn('id', $idArray)->sum('hrg_jual');

        $formatted = number_format($totalHarga, 0, ',', '.');

        return response()->json([
            'hrg_jual'  => $totalHarga,
            'formatted' => $formatted,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'hrg_jual'           => $request->hrg_jual ? (int) str_replace(['.', ','], '', $request->hrg_jual) : 0,
            'discount'           => $request->discount ? (int) str_replace(['.', ','], '', $request->discount) : 0,
            'pembayaran_booking' => $request->pembayaran_booking ? (int) str_replace(['.', ','], '', $request->pembayaran_booking) : 0,
            'dp_kredit'          => $request->dp_kredit ? (int) str_replace(['.', ','], '', $request->dp_kredit) : 0,
            'inhouse_tenor'      => $request->inhouse_tenor ?? 0,
            'inhouse_perbulan'   => $request->inhouse_perbulan ? (int) str_replace(['.', ','], '', $request->inhouse_perbulan) : 0,
        ]);

        $request->validate([
            'tgl_terima'          => 'required|date',
            'nama_lengkap'        => 'required|string|max:150',
            'no_ktp'              => 'required',
            'tempat_lahir'        => 'required',
            'tgl_lahir'           => 'required|date',
            'no_telp'             => 'required',
            'alamat'              => 'required',
            'email'               => 'nullable|email|max:75',
            'id_lokasi'           => 'required|integer|exists:lokasi_kavling,id',
            'id_kavling'          => 'required|array|min:1',
            'id_kavling.*'        => 'required|integer|distinct|exists:kavling_peta,id',
            'id_status_progres'   => 'required|exists:progres_list_penjualan,id',
            'id_marketing'        => 'nullable|exists:marketing,id',
            'discount'            => 'numeric|min:0',
            'id_bank'             => 'required|exists:bank,id',
            'hrg_jual'            => 'required|numeric',
            'jenis_pembelian'     => 'required|in:Booking,Cash Keras,Kredit',

            'pembayaran_booking'  => 'exclude_unless:jenis_pembelian,Booking|nullable|required_if:jenis_pembelian,Booking|numeric|min:0',
            'tgl_batas_booking'   => 'exclude_unless:jenis_pembelian,Booking|nullable|required_if:jenis_pembelian,Booking|date|after_or_equal:tgl_terima',

            'dp_kredit'           => 'exclude_unless:jenis_pembelian,Kredit|nullable|required_if:jenis_pembelian,Kredit|numeric|min:0',
            'inhouse_tenor'       => 'exclude_unless:jenis_pembelian,Kredit|nullable|required_if:jenis_pembelian,Kredit|integer|min:1',
            'inhouse_perbulan'    => 'exclude_unless:jenis_pembelian,Kredit|nullable|required_if:jenis_pembelian,Kredit|numeric|gt:0',
            'inhouse_jatuh_tempo' => 'exclude_unless:jenis_pembelian,Kredit|nullable|required_if:jenis_pembelian,Kredit|date',
        ], [
            'tgl_terima.required'             => 'Tanggal terima wajib diisi!',
            'tgl_terima.date'                 => 'Tanggal terima harus berupa tanggal yang valid!',

            'nama_lengkap.required'           => 'Nama lengkap wajib diisi!',

            'no_ktp.required'                 => 'Nomor KTP wajib diisi!',

            'tempat_lahir.required'           => 'Tempat lahir wajib diisi!',

            'tgl_lahir.required'              => 'Tanggal lahir wajib diisi!',
            'tgl_lahir.date'                  => 'Tanggal lahir harus berupa tanggal yang valid!',

            'no_telp.required'                => 'Nomor telepon wajib diisi!',

            'alamat.required'                 => 'Alamat wajib diisi!',

            'email.email'                     => 'Format email tidak valid!',

            'id_lokasi.required'              => 'Lokasi kavling wajib dipilih!',

            'id_kavling.required'             => 'Kavling wajib dipilih!',
            'id_kavling.array'                => 'Data kavling tidak valid!',
            'id_kavling.*.exists'             => 'Kavling yang dipilih tidak ditemukan!',

            'id_status_progres.required'      => 'Tahap penjualan wajib dipilih!',

            'id_bank.required'                => 'Rekening wajib dipilih!',
            'id_bank.exists'                  => 'Bank yang dipilih tidak ditemukan!',

            'hrg_jual.required'               => 'Harga jual wajib diisi!',
            'hrg_jual.numeric'                => 'Harga jual harus berupa angka!',

            'jenis_pembelian.required'        => 'Skema pembayaran wajib dipilih!',

            'pembayaran_booking.required_if'  => 'Pembayaran booking wajib diisi!',
            'pembayaran_booking.numeric'      => 'Pembayaran booking harus berupa angka!',

            'tgl_batas_booking.required_if'   => 'Tanggal batas booking wajib diisi!',
            'tgl_batas_booking.date'          => 'Tanggal batas booking tidak valid!',

            'dp_kredit.required_if'           => 'DP kredit wajib diisi!',
            'dp_kredit.numeric'               => 'DP kredit harus berupa angka!',

            'inhouse_tenor.required_if'       => 'Tenor wajib diisi!',
            'inhouse_tenor.numeric'           => 'Tenor harus berupa angka!',
            'inhouse_tenor.min'               => 'Tenor minimal 1 bulan.',
            'inhouse_tenor.integer'           => 'Tenor harus berupa jumlah bulan bulat.',
            'inhouse_perbulan.gt'             => 'Cicilan per bulan harus lebih dari nol.',
            'jenis_pembelian.in'              => 'Pilih Booking, Cash Keras, atau Kredit.',
            'id_kavling.*.distinct'           => 'Kavling yang sama tidak boleh dipilih dua kali.',
            'tgl_batas_booking.after_or_equal' => 'Batas booking tidak boleh sebelum tanggal transaksi.',

            'inhouse_perbulan.required_if'    => 'Cicilan per bulan wajib diisi!',
            'inhouse_perbulan.numeric'        => 'Cicilan per bulan harus berupa angka!',

            'inhouse_jatuh_tempo.required_if' => 'Jatuh tempo wajib diisi!',
            'inhouse_jatuh_tempo.date'        => 'Tanggal jatuh tempo tidak valid!',
        ]);

        if ($request->jenis_pembelian == 'Booking') {
            $request->merge([
                'dp_kredit'           => null,
                'inhouse_tenor'       => null,
                'inhouse_perbulan'    => null,
                'inhouse_jatuh_tempo' => null,
            ]);
        }

        if ($request->jenis_pembelian == 'Cash Keras') {
            $request->merge([
                'pembayaran_booking'  => null,
                'tgl_batas_booking'   => null,
                'dp_kredit'           => null,
                'inhouse_tenor'       => null,
                'inhouse_perbulan'    => null,
                'inhouse_jatuh_tempo' => null,
            ]);
        }

        if ($request->jenis_pembelian == 'Kredit') {
            $request->merge([
                'pembayaran_booking' => null,
                'tgl_batas_booking'  => null,
            ]);
        }

        DB::beginTransaction();

        try {
            $initialPayment = $request->jenis_pembelian === 'Booking' ? $request->pembayaran_booking : ($request->jenis_pembelian === 'Kredit' ? $request->dp_kredit : 0);
            $kavlingList = (new PurchaseSelection)->availableUnits($request->id_kavling, (int) $request->id_lokasi, (int) $request->discount, (int) $initialPayment);
            $request->merge(['hrg_jual' => (int) $kavlingList->sum('hrg_jual')]);
            $prefix = LokasiKavling::where('id', $request->id_lokasi)
                ->value('nama_singkat');

            $prefix = $prefix ?: 'GDI';

            $latest = Customer::where('kode_customer', 'LIKE', $prefix . '-%')
                ->latest('kode_customer')
                ->sharedLock()
                ->first();

            if ($latest && preg_match('/' . preg_quote($prefix, '/') . '-(\d+)/', $latest->kode_customer, $match)) {
                $number  = (int) $match[1] + 1;
                $newKode = $prefix . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            } else {
                $newKode = $prefix . '-0001';
            }

            $customer = Customer::create([
                'kode_customer'       => $newKode,
                'nama_lengkap'        => $request->nama_lengkap,
                'no_ktp'              => $request->no_ktp,
                'no_ktp_p'            => $request->no_ktp_p,
                'tempat_lahir'        => $request->tempat_lahir,
                'tgl_lahir'           => $request->tgl_lahir,
                'no_telp'             => $request->no_telp,
                'jenis_kelamin'       => $request->jenis_kelamin,
                'email'               => $request->email ?? '',
                'npwp'                => $request->npwp,
                'alamat'              => $request->alamat,
                'alamat_domisili'     => $request->alamat_domisili,
                'pekerjaan'           => $request->pekerjaan,
                'id_lokasi'           => $request->id_lokasi,
                'id_status_progres'   => $request->id_status_progres,
                'no_wa'               => $request->no_telp,
                'discount'            => $request->discount,
                'id_marketing'        => $request->filled('id_marketing') ? $request->id_marketing : 0,
                'ket_cashback'        => $request->ket_cashback,
                'jenis_pembelian'     => $request->jenis_pembelian,
                'inhouse_perbulan'    => $request->inhouse_perbulan,
                'inhouse_tenor'       => $request->inhouse_tenor,
                'inhouse_jatuh_tempo' => $request->inhouse_jatuh_tempo,
                'pembayaran_booking'  => $request->pembayaran_booking,
                'tgl_batas_booking'   => $request->tgl_batas_booking,
                'dp_kredit'           => $request->dp_kredit,
                'id_bank'             => $request->id_bank,
            ]);

            PersyaratanLegal::create([
                'id_customer' => $customer->id,
            ]);

            $totalNominalPiutang = $kavlingList->sum('hrg_jual') - $request->discount;

            foreach ($kavlingList as $kavling) {

                TransaksiKavling::create([
                    'id_customer' => $customer->id,
                    'id_kavling'  => $kavling->id,
                    'tgl_terima'  => $request->tgl_terima,
                    'hrg_rumah'   => $kavling->hrg_jual,
                ]);

            }

            $nominalBayar         = 0;
            $statusPiutang        = 1;
            $tglPelunasan         = null;
            $kategoriPemasukan    = null;
            $keteranganPemasukan  = null;

            if ($request->jenis_pembelian == 'Booking') {
                $nominalBayar        = $request->pembayaran_booking ?? 0;
                $kategoriPemasukan   = 25;
                $keteranganPemasukan = 'Pembayaran Booking Kavling';
            }

            if ($request->jenis_pembelian == 'Kredit') {
                $nominalBayar        = $request->dp_kredit ?? 0;
                $kategoriPemasukan   = 26;
                $keteranganPemasukan = 'Pembayaran DP Kredit Kavling';
            }

            if ($request->jenis_pembelian == 'Cash Keras') {
                $nominalBayar        = $totalNominalPiutang;
                $statusPiutang       = 2;
                $tglPelunasan        = now()->toDateString();
                $kategoriPemasukan   = 27;
                $keteranganPemasukan = 'Pelunasan Cash Keras Kavling';
            }

            $sisaBayar = $totalNominalPiutang - $nominalBayar;

            if ($sisaBayar <= 0) {
                $statusPiutang = 2;
                $sisaBayar     = 0;
                $tglPelunasan  = now()->toDateString();
            }

            $namaKavling = $kavlingList
                ->map(function ($item) {
                    return $item->lokasi->nama_kavling . ' Blok ' . $item->kode_kavling;
                })
                ->implode(', ');

            $deskripsiPiutang = 'Harga Tanah Kavling di ' . $namaKavling;

            $piutang = Piutang::create([
                'id_bank'               => $request->id_bank,
                'id_customer'           => $customer->id,
                'tanggal_piutang'       => $request->tgl_terima,
                'deskripsi'             => $deskripsiPiutang,
                'nominal'               => $totalNominalPiutang,
                'status'                => $statusPiutang,
                'terbayar'              => $nominalBayar,
                'sisa_bayar'            => $sisaBayar,
                'tgl_pelunasan'         => $tglPelunasan,
                'id_kategori_transaksi' => 0,
            ]);

            if ($nominalBayar > 0) {
                Pemasukan::create([
                    'id_bank'               => $request->id_bank,
                    'id_piutang'            => $piutang->id,
                    'id_customer'           => $customer->id,
                    'id_lokasi'             => $request->id_lokasi,
                    'tanggal'               => $request->tgl_terima,
                    'nominal'               => $nominalBayar,
                    'id_kategori_transaksi' => $kategoriPemasukan,
                    'keterangan'            => $keteranganPemasukan . ' - ' . $customer->nama_lengkap,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi pembelian berhasil disimpan.',
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Transaksi belum tersimpan. Silakan coba kembali.',
            ], 500);
        }
    }

    public function edit($id)
    {
        $cust = Customer::with(['lokasi', 'transaksiKavling.kavling', 'piutangs'])->findOrFail($id);

        $marketing = Marketing::all();
        $progres   = ListPenjualan::all();
        $lokasi    = LokasiKavling::all();
        $bankList  = Bank::all();

        return view(
            'admin.customer.customer.edit',
            compact('cust', 'marketing', 'progres', 'lokasi', 'bankList')
        );
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $request->validate([
            'nama_lengkap'      => 'required|string|max:150',
            'no_ktp'            => 'required|string|max:25',
            'tempat_lahir'      => 'required|string|max:100',
            'tgl_lahir'         => 'required|date',
            'no_telp'           => 'required|string|max:25',
            'alamat'            => 'required',
            'email'             => 'nullable|email|max:75',
            'id_status_progres' => 'required|integer|exists:progres_list_penjualan,id',
            'id_marketing'      => 'nullable|exists:marketing,id',
        ], [
            'nama_lengkap.required'      => 'Nama lengkap wajib diisi!',

            'no_ktp.required'            => 'Nomor KTP wajib diisi!',

            'tempat_lahir.required'      => 'Tempat lahir wajib diisi!',

            'tgl_lahir.required'         => 'Tanggal lahir wajib diisi!',
            'tgl_lahir.date'             => 'Tanggal lahir harus berupa tanggal yang valid!',

            'no_telp.required'           => 'Nomor telepon wajib diisi!',

            'alamat.required'            => 'Alamat wajib diisi!',

            'email.email'                => 'Format email tidak valid!',

            'id_status_progres.required' => 'Tahap penjualan wajib dipilih!',

            'id_marketing.exists'        => 'Marketing yang dipilih tidak ditemukan!',
        ]);

        DB::beginTransaction();

        try {

            $customer->update([
                'nama_lengkap'      => $request->nama_lengkap,
                'no_ktp'            => $request->no_ktp,
                'no_ktp_p'          => $request->no_ktp_p,
                'tempat_lahir'      => $request->tempat_lahir,
                'tgl_lahir'         => $request->tgl_lahir,
                'no_telp'           => $request->no_telp,
                'no_wa'             => $request->no_telp,
                'jenis_kelamin'     => $request->jenis_kelamin,
                'email'             => $request->email ?? '',
                'npwp'              => $request->npwp,
                'alamat'            => $request->alamat,
                'alamat_domisili'   => $request->alamat_domisili,
                'pekerjaan'         => $request->pekerjaan,
                'id_status_progres' => $request->id_status_progres,
                'id_marketing'      => $request->filled('id_marketing') ? $request->id_marketing : 0,
                'ket_cashback'      => $request->ket_cashback,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data customer berhasil diupdate',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Perubahan belum tersimpan. Silakan coba kembali.',
            ], 500);
        }
    }

    public function cetakData(Request $request)
    {
        $query = Customer::with(['marketing', 'lokasi', 'kavling', 'progres'])->where('stt_arsip', 0);

        if ($request->lokasi) {
            $query->where('id_lokasi', $request->lokasi);
        }

        $data = $query->get();

        $namaKavling = '';
        if ($request->lokasi) {
            $namaKavling = LokasiKavling::where('id', $request->lokasi)
                ->value('nama_kavling') ?? '';
        }

        // 2. Setup PDF
        $pdf = new TCPDF('P', 'mm', 'A4');
        $pdf->SetTitle('Data Customer');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        $pengaturanMedia = KonfigurasiMedia::where('jenis_data', 'kop surat')->first();
        $kopSuratPath    = null;

        if ($pengaturanMedia && $pengaturanMedia->nama_file) {
            $cekPath = public_path('config_media/' . $pengaturanMedia->nama_file);
            if (file_exists($cekPath)) {
                $kopSuratPath = $cekPath;
            }
        }

        if ($kopSuratPath) {
            // Tampilkan Gambar Full Width (210mm) di (0,0)
            $pdf->Image($kopSuratPath, 0, 0, 210);
        } else {
            // Fallback: Logo Default
            $logoPath = public_path('assets/img/header.png');
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 20, 5, 30);
            }
        }

        // --- GARIS PEMBATAS DIHAPUS SESUAI PERMINTAAN ---

        // 4. Judul Laporan
        // Kita beri jarak aman (Y=35) agar tidak menabrak gambar kop surat
        $pdf->SetY(35);

        $pdf->SetFont('Times', 'B', 12);
        $pdf->SetTextColor(218, 0, 0); // Warna Merah
        $pdf->Cell(190, 8, 'DATA CUSTOMER', 0, 1, 'C');

        // Sub-Judul Lokasi (Jika ada filter)
        if (! empty($namaKavling)) {
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(190, 5, 'Lokasi: ' . strtoupper($namaKavling), 0, 1, 'C');
        }

        $pdf->Ln(5);

        // 5. Tabel Data
        $pdf->SetFont('Times', 'B', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(252, 203, 53); // Warna Kuning

        $tableWidth = 165;
        $pageWidth  = $pdf->getPageWidth();
        $startX     = ($pageWidth - $tableWidth) / 2;

        $pdf->SetX($startX);
        $pdf->Cell(10, 7, 'NO', 1, 0, 'C', true);
        $pdf->Cell(45, 7, 'PEMOHON', 1, 0, 'L', true);
        $pdf->Cell(15, 7, 'UNIT', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'NO. TELP', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'MARKETING', 1, 0, 'L', true);
        $pdf->Cell(30, 7, 'STATUS', 1, 1, 'C', true);

        $pdf->SetFont('Times', '', 8);
        $no = 1;

        if ($data->count() > 0) {
            foreach ($data as $d) {
                // Perbaikan untuk relasi Many-to-Many: Menggabungkan kode kavling
                $unitKavling = $d->kavling->pluck('kode_kavling')->implode(', ');
                $unitKavling = ! empty($unitKavling) ? $unitKavling : '-';

                $pdf->SetX($startX); // Reset X ke tengah setiap baris

                $pdf->Cell(10, 7, $no++, 1, 0, 'C');
                $pdf->Cell(45, 7, $d->nama_lengkap, 1, 0, 'L');
                $pdf->Cell(15, 7, $unitKavling, 1, 0, 'C'); // Menggunakan string gabungan
                $pdf->Cell(25, 7, (! empty($d->no_wa) ? $d->no_wa : ($d->no_telp ?? '-')), 1, 0, 'C');
                $pdf->Cell(40, 7, ($d->marketing->nama_marketing ?? '-'), 1, 0, 'L');
                $pdf->Cell(30, 7, ($d->progres->status_progres ?? '-'), 1, 1, 'C');
            }
        } else {
            $pdf->SetX($startX);
            $pdf->Cell(165, 7, 'Tidak ada data ditemukan', 1, 1, 'C');
        }

        $pdf->Output('data_customer.pdf', 'I');
        exit;
    }

    public function cetakFormSubsidi($id_customer)
    {
        Carbon::setLocale('id');

        $customer = Customer::with(['kavling', 'lokasiKavling'])
            ->findOrFail($id_customer);

        $konfigurasi = KonfigurasiAplikasi::first();

        $alamat      = $konfigurasi->alamat ?? '-';
        $email       = $konfigurasi->email ?? '-';
        $no_telp_dev = $konfigurasi->telp ?? '-';

        $noKavling     = $customer->kavling->pluck('no')->first() ?? '-';
        $kodeKavling   = $customer->kavling->pluck('kode_kavling')->implode(', ') ?? '-';
        $luasTanah     = $customer->kavling->sum('luas_tanah');
        $sumHrgKavling = $customer->kavling->sum('hrg_jual');

        // Net Harga Jual (Gross Kavling - Discount)
        $netHargaJual = $sumHrgKavling - ($customer->discount ?? 0);

        // DP Logic
        $dpTable = $customer->dp_kredit ?? 0;
        $booking = $customer->pembayaran_booking ?? 0;
        $totalDP = $dpTable + $booking;

        $sisa = $netHargaJual - $totalDP;

        if ($sisa < 0) {
            $sisa = 0;
        }

        $templatePath = public_path('template/ppjb_template.docx');

        $templateProcessor = new TemplateProcessor($templatePath);

        $tanggalNow  = Carbon::now();
        $bulanRomawi = $this->bulanRomawi($tanggalNow->month);
        $bulan       = $tanggalNow->translatedFormat('F');
        $tahun       = $tanggalNow->year;
        $hari        = $tanggalNow->translatedFormat('l');
        $tanggal     = $tanggalNow->translatedFormat('d');

        $lastNumber = Customer::count() + 1;
        $nomorUrut  = str_pad($lastNumber, 3, '0', STR_PAD_LEFT);

        $templateProcessor->setValue('alamat', $alamat);
        $templateProcessor->setValue('email', $email);
        $templateProcessor->setValue('no_telp_dev', $no_telp_dev);

        $templateProcessor->setValue('no', $nomorUrut);
        $templateProcessor->setValue('bulan_romawi', $bulanRomawi);
        $templateProcessor->setValue('tahun', $tahun);
        $templateProcessor->setValue('hari', $hari);
        $templateProcessor->setValue('tanggal', $tanggal);
        $templateProcessor->setValue('bulan', $bulan);

        $templateProcessor->setValue('nama_cust', $customer->nama_lengkap ?? '-');
        $templateProcessor->setValue('no_telp', $customer->no_telp ?? '-');
        $templateProcessor->setValue('alamat_ktp', $customer->alamat ?? '-');
        $templateProcessor->setValue('nik', $customer->no_ktp ?? '-');

        $templateProcessor->setValue('kode_kavling', $kodeKavling);
        $templateProcessor->setValue('no_kavling', $noKavling);
        $templateProcessor->setValue('luas_tanah', $luasTanah);

        $templateProcessor->setValue('harga_jual', number_format($netHargaJual, 0, ',', '.'));
        $templateProcessor->setValue('harga_jual_terbilang', $this->terbilang($netHargaJual));

        $templateProcessor->setValue('dp', number_format($totalDP, 0, ',', '.'));
        $templateProcessor->setValue('dp_terbilang', $this->terbilang($totalDP));

        $templateProcessor->setValue('sisa_bayar', number_format($sisa, 0, ',', '.'));
        $templateProcessor->setValue('sisa_bayar_terbilang', $this->terbilang($sisa));

        $templateProcessor->setValue('tenor', $customer->inhouse_tenor ?? 0);
        $templateProcessor->setValue('cicilan', number_format($customer->inhouse_perbulan ?? 0, 0, ',', '.'));

        $fileName = 'akad_' . $customer->id . '_' . $customer->nama_lengkap . '.docx';
        $savePath = storage_path('app/public/' . $fileName);
        $templateProcessor->saveAs($savePath);

        return response()->download($savePath)->deleteFileAfterSend(true);
    }

    private function terbilang($angka)
    {
        $f = new \NumberFormatter("id", \NumberFormatter::SPELLOUT);
        return ucwords($f->format($angka)) . ' Rupiah';
    }

    private function convertTanggal($number)
    {
        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];

        if ($number < 12) {
            return $huruf[$number];
        } elseif ($number < 20) {
            return $this->convertTanggal($number - 10) . " Belas";
        } elseif ($number < 100) {
            return $this->convertTanggal(floor($number / 10)) . " Puluh " . $this->convertTanggal($number % 10);
        } elseif ($number < 200) {
            return "Seratus " . $this->convertTanggal($number - 100);
        } elseif ($number < 1000) {
            return $this->convertTanggal(floor($number / 100)) . " Ratus " . $this->convertTanggal($number % 100);
        } elseif ($number < 2000) {
            return "Seribu " . $this->convertTanggal($number - 1000);
        } elseif ($number < 1000000) {
            return $this->convertTanggal(floor($number / 1000)) . " Ribu " . $this->convertTanggal($number % 1000);
        } elseif ($number < 1000000000) {
            return $this->convertTanggal(floor($number / 1000000)) . " Juta " . $this->convertTanggal($number % 1000000);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $request = request();
            $request->validate([
                'tgl_cancel'    => 'required|date',
                'alasan_cancel' => 'required',
            ], [
                'tgl_cancel.required'    => 'Tanggal cancel wajib diisi!',
                'tgl_cancel.date'        => 'Format tanggal tidak valid!',
                'alasan_cancel.required' => 'Alasan cancel wajib diisi!',
            ]);

            $data = Customer::findOrFail($id);
            $data->update([
                'stt_arsip'     => 1,
                'tgl_cancel'    => $request->tgl_cancel,
                'alasan_cancel' => $request->alasan_cancel,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil di cancel.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancel customer: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Data gagal di cancel.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($id);

            $duplicateKavling = TransaksiKavling::whereIn('id_kavling', function ($q) use ($customer) {
                $q->select('id_kavling')
                    ->from('transaksi_kavling')
                    ->where('id_customer', $customer->id);
            })
                ->where('id_customer', '!=', $customer->id)
                ->whereHas('customer', function ($q) {
                    $q->where('stt_arsip', 0);
                })
                ->exists();

            if ($duplicateKavling) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kavling sudah ada kepemilikan lain',
                ], 422);
            }

            $customer->update([
                'stt_arsip'     => 0,
                'tgl_cancel'    => null,
                'alasan_cancel' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dikembalikan',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restore customer: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengembalikan data',
                'error'   => $e->getMessage(),
            ], 500);
        }
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
}
