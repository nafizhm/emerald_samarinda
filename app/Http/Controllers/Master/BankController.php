<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = Bank::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($permissions) {
                    $editUrl = route('bank.edit', $row->id);
                    $deleteUrl = route('bank.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';
                    if (isset($permissions['edit']) && $permissions['edit'] == 1) {
                        $btn .= '<button class="btn btn-primary btn-sm mx-1 edit-button" data-id="' . e($row->id) . '" data-url="' . e($editUrl) . '" data-bs-toggle="modal" data-bs-target="#modalForm">Edit</button>';
                    }
                    if (isset($permissions['hapus']) && $permissions['hapus'] == 1) {
                        $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">'
                            . csrf_field() . method_field('DELETE') .
                            '<button type="submit" class="delete-button btn btn-danger btn-sm mx-1">Hapus</button></form>';
                    }
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.master.bank.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'        => 'required|string',
            'no_rek'      => 'required|string',
            'pemilik_rek' => 'required|string',
        ], [
            'nama.required'        => 'Nama bank wajib diisi.',
            'no_rek.required'      => 'Nomor rekening wajib diisi.',
            'pemilik_rek.required' => 'Pemilik rekening wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            Bank::create([
                'nama'        => $request->nama,
                'no_rek'      => $request->no_rek,
                'pemilik_rek' => $request->pemilik_rek,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Data gagal disimpan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $data = Bank::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $data
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama'        => 'required|string',
            'no_rek'      => 'required|string',
            'pemilik_rek' => 'required|string',
        ], [
            'nama.required'        => 'Nama bank wajib diisi.',
            'no_rek.required'      => 'Nomor rekening wajib diisi.',
            'pemilik_rek.required' => 'Pemilik rekening wajib diisi.',
        ]);

        $data = Bank::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $data->update([
                'nama'        => $request->nama,
                'no_rek'      => $request->no_rek,
                'pemilik_rek' => $request->pemilik_rek,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Data gagal diperbarui',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $data = Bank::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        DB::beginTransaction();
        try {
            $data->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage()
            ]);
        }
    }
}
