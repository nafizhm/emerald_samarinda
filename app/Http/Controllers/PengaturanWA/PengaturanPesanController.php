<?php

namespace App\Http\Controllers\PengaturanWA;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\TemplatePesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PengaturanPesanController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();
        if ($request->ajax()) {
            $data = TemplatePesan::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($permissions) {
                    $editUrl   = route('template-pesan.edit', $row->id);
                    $deleteUrl = route('template-pesan.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';
                    if ($permissions['edit']) {
                        $btn .= '<button class="btn btn-primary btn-sm mx-1 edit-button"
                                data-id="' . e($row->id) . '"
                                data-url="' . e($editUrl) . '">Edit</button>';
                    }

                    if ($permissions['hapus']) {
                        $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="delete-button btn btn-danger btn-sm mx-1">
                                    Hapus
                                </button>
                                </form>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        $permissions = HakAksesController::getUserPermissions();

        return view('admin.pengaturan_wa.template_pesan.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $rules = [
            'nama_template' => 'required|string|max:50',
            'jenis_pesan'   => 'required|string|max:15|unique:template_pesan,jenis_pesan',
            'isi_template'  => 'required|string',
        ];

        $messages = [
            'nama_template.required' => 'Judul pesan wajib diisi.',
            'nama_template.string'   => 'Judul pesan harus berupa teks.',
            'nama_template.max'      => 'Judul pesan maksimal 50 karakter.',

            'jenis_pesan.required'   => 'Jenis wajib diisi.',
            'jenis_pesan.unique'     => 'Jenis sudah ada.',
            'jenis_pesan.string'     => 'Jenis pesan harus berupa teks.',
            'jenis_pesan.max'        => 'Jenis maksimal 15 karakter.',

            'isi_template.required'  => 'Isi pesan wajib diisi.',
            'isi_template.string'    => 'Isi pesan harus berupa teks.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            TemplatePesan::create([
                'nama_template' => $request->nama_template,
                'jenis_pesan'   => $request->jenis_pesan,
                'isi_template'  => $request->isi_template,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template pesan berhasil ditambahkan.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Template pesan gagal ditambahkan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $dt = TemplatePesan::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $dt,
            'message' => 'Berhasil mengambil data'
        ]);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'nama_template' => 'required|string|max:50',
            'jenis_pesan'   => 'required|string|max:15|unique:template_pesan,jenis_pesan,' . $id,
            'isi_template'  => 'required|string',
        ];

        $messages = [
            'nama_template.required' => 'Judul pesan wajib diisi.',
            'nama_template.string'   => 'Judul pesan harus berupa teks.',
            'nama_template.max'      => 'Judul pesan maksimal 50 karakter.',

            'jenis_pesan.required'   => 'Jenis wajib diisi.',
            'jenis_pesan.unique'     => 'Jenis sudah ada.',
            'jenis_pesan.string'     => 'Jenis pesan harus berupa teks.',
            'jenis_pesan.max'        => 'Jenis maksimal 15 karakter.',

            'isi_template.required'  => 'Isi pesan wajib diisi.',
            'isi_template.string'    => 'Isi pesan harus berupa teks.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            TemplatePesan::where('id', $id)->update([
                'nama_template' => $request->nama_template,
                'jenis_pesan'   => $request->jenis_pesan,
                'isi_template'  => $request->isi_template,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template pesan berhasil diubah.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Template pesan gagal diubah.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            TemplatePesan::where('id', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template pesan berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Template pesan gagal dihapus.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
