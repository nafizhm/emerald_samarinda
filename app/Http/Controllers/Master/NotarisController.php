<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Notaris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class NotarisController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $opd = Notaris::orderBy('id', 'asc')->get();

            return DataTables::of($opd)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('notaris.edit', $row->id);
                    $deleteUrl = route('notaris.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';
                    $btn .= '<button class="btn btn-primary btn-sm mx-1 edit-button" data-id="' . e($row->id) . '"
                        data-url="' . e($editUrl) . '" data-toggle="modal" data-target="#modalForm">
                        Edit
                    </button>';

                    $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="delete-button btn btn-danger btn-sm mx-1">
                                Hapus
                            </button>
                        </form>';

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.master.notaris.index', compact('permissions'));
    }

    public function edit($id)
    {
        $list = Notaris::find($id);

        if (!$list) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $list,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_notaris' => 'required|string|max:255',
            'alamat_notaris' => 'required|string|max:500',
            'telp_notaris' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]+$/'],
            'keterangan_notaris' => 'required|string|max:1000',
        ], [
            'nama_notaris.required' => 'Nama notaris wajib diisi.',
            'nama_notaris.string' => 'Nama notaris harus berupa teks.',
            'nama_notaris.max' => 'Nama notaris maksimal 255 karakter.',

            'alamat_notaris.required' => 'Alamat notaris wajib diisi.',
            'alamat_notaris.string' => 'Alamat notaris harus berupa teks.',
            'alamat_notaris.max' => 'Alamat notaris maksimal 500 karakter.',

            'telp_notaris.required' => 'Nomor telepon notaris wajib diisi.',
            'telp_notaris.string' => 'Nomor telepon notaris harus berupa teks.',
            'telp_notaris.max' => 'Nomor telepon notaris maksimal 20 karakter.',
            'telp_notaris.regex' => 'Nomor telepon notaris formatnya harus angka, spasi, strip, atau diawali +.',

            'keterangan_notaris.required' => 'Keterangan notaris wajib diisi.',
            'keterangan_notaris.string' => 'Keterangan notaris harus berupa teks.',
            'keterangan_notaris.max' => 'Keterangan notaris maksimal 1000 karakter.',
        ]);

        DB::beginTransaction();

        try {
            $db = [
                'nama_notaris'          => $request->nama_notaris,
                'alamat_notaris'          => $request->alamat_notaris,
                'telp_notaris'              => $request->telp_notaris,
                'keterangan_notaris'              => $request->keterangan_notaris,
            ];

            Notaris::create($db);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Data gagal disimpan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = Notaris::findOrFail($id);

        $request->validate([
            'nama_notaris' => 'required|string|max:255',
            'alamat_notaris' => 'required|string|max:500',
            'telp_notaris' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]+$/'],
            'keterangan_notaris' => 'required|string|max:1000',
        ], [
            'nama_notaris.required' => 'Nama notaris wajib diisi.',
            'nama_notaris.string' => 'Nama notaris harus berupa teks.',
            'nama_notaris.max' => 'Nama notaris maksimal 255 karakter.',

            'alamat_notaris.required' => 'Alamat notaris wajib diisi.',
            'alamat_notaris.string' => 'Alamat notaris harus berupa teks.',
            'alamat_notaris.max' => 'Alamat notaris maksimal 500 karakter.',

            'telp_notaris.required' => 'Nomor telepon notaris wajib diisi.',
            'telp_notaris.string' => 'Nomor telepon notaris harus berupa teks.',
            'telp_notaris.max' => 'Nomor telepon notaris maksimal 20 karakter.',
            'telp_notaris.regex' => 'Nomor telepon notaris formatnya harus angka, spasi, strip, atau diawali +.',

            'keterangan_notaris.required' => 'Keterangan notaris wajib diisi.',
            'keterangan_notaris.string' => 'Keterangan notaris harus berupa teks.',
            'keterangan_notaris.max' => 'Keterangan notaris maksimal 1000 karakter.',
        ]);

        DB::beginTransaction();

        try {
            $db = [
                'nama_notaris'          => $request->nama_notaris,
                'alamat_notaris'          => $request->alamat_notaris,
                'telp_notaris'              => $request->telp_notaris,
                'keterangan_notaris'              => $request->keterangan_notaris,
            ];

            $data->update($db);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Data gagal disimpan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $data = Notaris::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data notaris tidak ditemukan.'
            ], 404);
        }

        try {
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal dihapus: ' . $e->getMessage()
            ], 500);
        }
    }
}
