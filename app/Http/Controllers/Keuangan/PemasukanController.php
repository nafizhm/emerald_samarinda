<?php
namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\KategoriTransaksi;
use App\Models\Pemasukan;
use App\Models\Piutang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PemasukanController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = Pemasukan::orderBy('tanggal', 'desc')->orderBy('id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row->tanggal)->translatedFormat('j F Y');
                })
                ->editColumn('nominal', function ($row) {
                    return '
                    <div class="d-flex justify-content-between harga-format w-100">
                        <span>Rp.</span>
                        <span>' . number_format($row->nominal, 0, ',', '.') . '</span>
                    </div>';
                })
                ->addColumn('rekening', function ($row) {
                    $bank = Bank::find($row->id_bank);
                    return $bank ? $bank->nama : 'Bank Tidak Diketahui';
                })
                ->addColumn('id_kategori_transaksi', function ($row) {
                    $kategori = KategoriTransaksi::find($row->id_kategori_transaksi);
                    $nama     = $kategori ? $kategori->kategori : 'Tidak Diketahui';
                    return $nama;
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->input('search.value') != '') {
                        $search = $request->input('search.value');

                        $bankIds     = Bank::where('nama', 'like', "%$search%")->pluck('id');
                        $kategoriIds = KategoriTransaksi::where('kategori', 'like', "%$search%")->pluck('id');

                        $query->where(function ($q) use ($bankIds, $kategoriIds, $search) {
                            $q->whereIn('id_bank', $bankIds)
                                ->orWhereIn('id_kategori_transaksi', $kategoriIds)
                                ->orWhere('keterangan', 'like', "%$search%");
                        });
                    }
                })
                ->filterColumn('tanggal', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->WhereDate('tanggal', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function ($row) use ($permissions) {
                    $editUrl   = route('pemasukan.edit', $row->id);
                    $detailUrl = route('pemasukan.show', $row->id);
                    $deleteUrl = route('pemasukan.destroy', $row->id);

                    $disabledKategori = in_array($row->id_kategori_transaksi, [25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36]);

                    $btn = '<div class="d-flex justify-content-center">';

                    if ($row->id_hutang != 0 || $row->id_customer != 0 || $row->id_mutasi != 0) {
                        $btn .= '<button class="btn btn-primary btn-sm mx-1 detail-button"
            data-id="' . e($row->id) . '"
            data-url="' . e($detailUrl) . '">
            Detail
        </button>';
                    } else {
                        if ($permissions['edit']) {
                            $btn .= '<button class="btn btn-primary btn-sm mx-1 edit-button ' . ($disabledKategori ? 'disabled' : '') . '"
                data-id="' . e($row->id) . '"
                data-url="' . ($disabledKategori ? '#' : e($editUrl)) . '" ' . ($disabledKategori ? 'disabled' : '') . '>Edit</button>';
                        }
                    }

                    if ($permissions['hapus']) {
                        $btn .= '<form action="' . ($disabledKategori ? '#' : e($deleteUrl)) . '" method="POST" style="display:inline;">
        ' . csrf_field() . method_field('DELETE') . '
        <button type="submit" class="delete-button btn btn-danger btn-sm mx-1 ' . ($disabledKategori ? 'disabled' : '') . '" ' . ($disabledKategori ? 'disabled' : '') . '>
            Hapus
        </button>
        </form>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })

                ->rawColumns(['action', 'tanggal', 'nominal', 'id_kategori_transaksi'])
                ->make(true);
        }

        $kategoriTransaksi = KategoriTransaksi::where('jenis_kategori', 'PEMASUKAN')
            ->whereNotIn('id', [25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36])
            ->get();

        $kategoriTransaksiDetail = KategoriTransaksi::where('jenis_kategori', 'PEMASUKAN')
            ->whereIn('id', [25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36])
            ->get();

        $PiutangList = Piutang::where('status', 1)->where('id_customer', 0)->get();

        $bankList = Bank::all();

        return view('admin.keuangan.pemasukan.index', compact('permissions', 'kategoriTransaksi', 'kategoriTransaksiDetail', 'PiutangList', 'bankList'));
    }

    public function edit($id)
    {
        $list = Pemasukan::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ]);
    }

    public function show($id)
    {
        $list = Pemasukan::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ]);
    }

    public function store(Request $request)
    {
        $values = $this->validatePayment($request);
        DB::transaction(function () use ($values) {
            $piutang = !empty($values['id_piutang']) ? Piutang::lockForUpdate()->findOrFail($values['id_piutang']) : null;
            $this->assertWithinBalance($piutang, $values['nominal']);
            Pemasukan::create($values + $this->paymentOwner($piutang) + ['id_hutang' => 0, 'id_mutasi' => 0, 'lampiran' => '']);
            if ($piutang) $this->syncReceivable($piutang);
        });
        return response()->json(['status' => 'success']);
    }

    public function update(Request $request, $id)
    {
        $existing = Pemasukan::findOrFail($id);
        $request->merge([
            'id_kategori_transaksi' => $existing->id_kategori_transaksi,
            'id_piutang' => $request->input('id_piutang', $existing->id_piutang),
        ]);
        $values = $this->validatePayment($request);
        DB::transaction(function () use ($values, $id) {
            $payment = Pemasukan::lockForUpdate()->findOrFail($id);
            $oldId = $payment->id_piutang;
            $receivables = Piutang::whereIn('id', array_filter([$oldId, $values['id_piutang'] ?? null]))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $target = $receivables->get($values['id_piutang'] ?? 0);
            $available = $target ? (float) $target->sisa_bayar + ($target->id == $oldId ? (float) $payment->nominal : 0) : null;
            if ($available !== null && $values['nominal'] > $available) throw ValidationException::withMessages(['nominal' => 'Nominal tidak boleh melebihi sisa piutang.']);
            $payment->update($values + $this->paymentOwner($target));
            foreach ($receivables as $piutang) $this->syncReceivable($piutang);
        });
        return response()->json(['status' => 'success']);
    }

    public function destroy($id)
    {
        $path = DB::transaction(function () use ($id) {
            $payment = Pemasukan::lockForUpdate()->findOrFail($id);
            $piutang = $payment->id_piutang ? Piutang::lockForUpdate()->find($payment->id_piutang) : null;
            $path = $payment->lampiran;
            $payment->delete();
            if ($piutang) $this->syncReceivable($piutang);
            return $path;
        });
        if ($path && basename($path) === $path && file_exists(public_path('assets/keuangan/pemasukan/'.$path))) unlink(public_path('assets/keuangan/pemasukan/'.$path));
        return response()->json(['status' => 'success']);
    }

    private function validatePayment(Request $request): array
    {
        $request->merge(['nominal' => str_replace('.', '', (string) $request->nominal),
            'id_piutang' => $request->id_piutang ?: null]);
        $values = $request->validate([
            'tanggal' => 'required|date', 'nominal' => 'required|integer|min:1',
            'id_kategori_transaksi' => 'required|integer',
            'id_piutang' => 'nullable|required_if:id_kategori_transaksi,6|integer|min:1|exists:piutang,id',
            'keterangan' => 'nullable|string|max:5000',
        ]);
        $values['id_piutang'] = $values['id_piutang'] ?? 0;
        $values['keterangan'] = $values['keterangan'] ?? '';
        return $values;
    }

    private function paymentOwner(?Piutang $piutang): array
    {
        return ['id_customer' => $piutang?->id_customer ?? 0,
            'id_lokasi' => $piutang?->id_customer ? (Customer::find($piutang->id_customer)?->id_lokasi ?? 0) : 0,
            'id_bank' => $piutang?->id_bank ?? 0];
    }

    private function assertWithinBalance(?Piutang $piutang, int $amount): void
    {
        if ($piutang && $amount > $piutang->sisa_bayar) throw ValidationException::withMessages(['nominal' => 'Nominal tidak boleh melebihi sisa piutang.']);
    }

    private function syncReceivable(Piutang $piutang): void
    {
        $paid = (float) Pemasukan::where('id_piutang', $piutang->id)
            ->where(fn ($q) => $q->whereNull('keterangan')->orWhere('keterangan', '!=', 'GANTI NAMA'))->sum('nominal');
        $remaining = max(0, (float) $piutang->nominal - $paid);
        $piutang->update(['terbayar' => $paid, 'sisa_bayar' => $remaining, 'status' => $remaining <= 0 ? 2 : 1,
            'tgl_pelunasan' => $remaining <= 0 ? now()->toDateString() : null]);
    }
}
