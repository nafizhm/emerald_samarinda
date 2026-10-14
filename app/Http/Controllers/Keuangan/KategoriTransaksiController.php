<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\KategoriTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class KategoriTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = KategoriTransaksi::orderBy('id', 'asc')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($permissions) {
                    $showUrl = route('kategori-transaksi.show', $row->id);
                    $deleteUrl = route('kategori-transaksi.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';

                    if ($permissions['edit']) {
                        $btn .= '<button class="btn btn-primary me-1 btn-sm edit-button" data-id="' . e($row->id) . '" data-url="' . e($showUrl) . '">Edit</button>';
                    }

                    if ($permissions['hapus']) {
                        $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="delete-button btn btn-danger btn-sm ">Hapus</button>'
                            . '</form>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.keuangan.kategori_transaksi.index', compact('permissions'));
    }

    public function show($id)
    {
        $data = KategoriTransaksi::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'kode' => 'required|unique:kategori_transaksi,kode',
            'kategori' => 'required|unique:kategori_transaksi,kategori',
            'jenis_kategori' => 'required',
        ];

        $messages = [
            'kode.required' => 'Kode wajib diisi.',
            'kode.unique' => 'Kode sudah digunakan.',
            'kategori.required' => 'Kategori wajib diisi.',
            'kategori.unique' => 'Kategori sudah digunakan.',
            'jenis_kategori.required' => 'Jenis kategori wajib dipilih.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {

            $db = [
                'kode' => $request->kode,
                'kategori' => $request->kategori,
                'jenis_kategori' => $request->jenis_kategori,
                'stt_fix' => 0,

            ];

            KategoriTransaksi::create($db);

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = KategoriTransaksi::findOrFail($id);

        $rules = [
            'kode' => 'required|unique:kategori_transaksi,kode,' . $data->id . ',id',
            'kategori' => 'required|unique:kategori_transaksi,kategori,' . $data->id . ',id',
            'jenis_kategori' => 'required',
        ];

        $messages = [
            'kode.required' => 'Kode wajib diisi.',
            'kode.unique' => 'Kode sudah digunakan.',
            'kategori.required' => 'Kategori wajib diisi.',
            'kategori.unique' => 'Kategori sudah digunakan.',
            'jenis_kategori.required' => 'Jenis kategori wajib dipilih.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {

            $db = [
                'kode' => $request->kode,
                'kategori' => $request->kategori,
                'jenis_kategori' => $request->jenis_kategori,
                'stt_fix' => 0,

            ];

            $data->update($db);

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = KategoriTransaksi::findOrFail($id);

            $data->delete();

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
