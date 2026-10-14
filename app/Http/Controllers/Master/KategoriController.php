<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Kategori;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = Kategori::orderBy('stt_fix', 'desc')
                ->orderBy('id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($permissions) {
                    $showUrl   = route('kategori.edit', $row->id);
                    $deleteUrl = route('kategori.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';

                    $disabled = ($row->stt_fix == 1) ? 'disabled' : '';

                    if ($permissions['edit']) {
                        $btn .= '<button class="btn btn-primary btn-sm edit-button" ' . $disabled .
                            ' data-id="' . e($row->id) . '" data-url="' . e($showUrl) . '">Edit</button>';
                    }

                    if ($permissions['hapus']) {
                        $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="delete-button btn btn-danger btn-sm mx-2" ' . $disabled . '>Hapus</button>'
                            . '</form>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.master.kategori.index', compact('permissions'));
    }

    public function edit($id)
    {
        $data = Kategori::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message'  => 'Data tidak ditemukan',
                'data'   => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditemukan',
            'data'   => $data,
        ], 200);
    }

    public function store(Request $request)
    {
        $rules = [
            'kategori'       => 'required|unique:kategori,kategori',
            'jenis_kategori' => 'required|in:' . implode(',', Kategori::getJenisKategori()),
        ];

        $messages = [
            'kategori.required'       => 'Kategori wajib diisi.',
            'kategori.unique'         => 'Kategori sudah digunakan.',
            'jenis_kategori.required' => 'Jenis kategori wajib diisi.',
            'jenis_kategori.in'       => 'Jenis kategori tidak valid.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {

            $db = [
                'kategori'       => $request->kategori,
                'jenis_kategori' => $request->jenis_kategori,
                'stt_fix'        => 0,
            ];

            Kategori::create($db);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Data gagal disimpan.',
                'error'  => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = Kategori::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $rules = [
            'kategori'       => 'required|unique:kategori,kategori,' . $data->id . ',id',
            'jenis_kategori' => 'required|in:' . implode(',', Kategori::getJenisKategori()),
        ];

        $messages = [
            'kategori.required'       => 'Kategori wajib diisi.',
            'kategori.unique'         => 'Kategori sudah digunakan.',
            'jenis_kategori.required' => 'Jenis kategori wajib diisi.',
            'jenis_kategori.in'       => 'Jenis kategori tidak valid.',
        ];
        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {

            $db = [
                'kategori'       => $request->kategori,
                'jenis_kategori' => $request->jenis_kategori,
            ];

            $data->update($db);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Data gagal disimpan.',
                'error'  => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = Kategori::findOrFail($id);
            $data->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Data gagal dihapus.',
                'error'  => $e->getMessage(),
            ], 500);
        }
    }
}
