<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Marketing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MarketingController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $program = Marketing::query();

            return DataTables::of($program)
                ->addIndexColumn()

                ->addColumn('kode_marketing_render', function ($row) {
                    $imgFile = $row->foto
                        ? asset('photo/' . $row->foto)
                        : (str_contains(strtolower($row->jenis_kelamin), 'laki')
                            ? asset('assets/img/men.jpg')
                            : asset('assets/img/woman.jpg'));

                    return '<img src="' . $imgFile . '" width="30" height="30" class="me-2 align-middle rounded-circle" style="object-fit:cover;">' .
                        '<span class="align-middle">' . e($row->kode_marketing) . '</span>';
                })

                ->addColumn('alamat_render', function ($row) {
                    return e($row->alamat) . '<br> Telp: ' . e($row->no_telp);
                })

                ->addColumn('status_render', function ($row) {
                    return $row->status == 1
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-secondary">Tidak Aktif</span>';
                })

                ->addColumn('action', function ($row) use ($permissions) {
                    $editUrl = route('marketing.edit', $row->id);
                    $deleteUrl = route('marketing.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';
                    $btn .= '<button class="btn btn-primary btn-sm mx-1 edit-button" data-id="' . e($row->id) . '" data-url="' . e($editUrl) . '" data-toggle="modal" data-target="#modalForm">Edit</button>';
                    $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">'
                        . csrf_field() . method_field('DELETE') .
                        '<button type="submit" class="delete-button btn btn-danger btn-sm mx-1">Hapus</button></form>';
                    $btn .= '</div>';

                    return $btn;
                })

                ->rawColumns(['kode_marketing_render', 'alamat_render', 'status_render', 'action'])
                ->make(true);
        }

        $data = Marketing::first();

        return view('admin.marketing.index', compact('data', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_marketing' => 'required|string',
            'alamat'         => 'required|string|max:50',
            'jenis_kelamin'  => 'required|max:100',
            'pekerjaan'      => 'nullable',
            'no_telp'        => 'required',
            'foto'           => 'nullable',
            'status'         => 'required',
        ], [
            'nama_marketing.required' => 'Nama marketing wajib diisi.',
            'nama_marketing.string'   => 'Nama marketing harus berupa teks.',

            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.string'   => 'Alamat harus berupa teks.',
            'alamat.max'      => 'Alamat tidak boleh lebih dari 50 karakter.',

            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.max'      => 'Jenis kelamin tidak valid.',

            'no_telp.required' => 'Nomor telepon wajib diisi.',

            'foto.mimes' => 'Foto harus berformat jpg, jpeg, atau png.',
            'foto.max'   => 'Ukuran foto maksimal 2MB.',

            'status.required' => 'Status wajib dipilih.',
        ]);

        $lastMarketing = Marketing::where('kode_marketing', 'like', 'M-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = ($lastMarketing && preg_match('/M-(\d+)/', $lastMarketing->kode_marketing, $matches))
            ? intval($matches[1]) + 1
            : 1;

        $kodeMarketing = 'M-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $fileName = '';

        DB::beginTransaction();

        try {
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $extension = strtolower($file->getClientOriginalExtension());
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $cleanName = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $originalName);
                $webpName = time() . '_' . $cleanName . '.webp';

                $destinationPath = public_path('photo');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $image = null;
                    if ($extension === 'jpg' || $extension === 'jpeg') {
                        $image = imagecreatefromjpeg($file->getPathname());
                    } elseif ($extension === 'png') {
                        $image = imagecreatefrompng($file->getPathname());
                    }

                    if ($image) {
                        imagewebp($image, $destinationPath . '/' . $webpName, 80);
                        imagedestroy($image);
                        $fileName = $webpName;
                    }
                }
            }

            Marketing::create([
                'kode_marketing' => $kodeMarketing,
                'nama_marketing' => $request->nama_marketing,
                'alamat'         => $request->alamat,
                'jenis_kelamin'  => $request->jenis_kelamin,
                'pekerjaan'      => $request->pekerjaan ?? '',
                'no_telp'        => $request->no_telp,
                'foto'           => $fileName,
                'status'         => $request->status,
                'id_level'       => $request->id_level ?? '0',
                'stt_marketing'  => $request->stt_marketing ?? '0',
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

    public function edit($id_marketing)
    {
        $data = Marketing::find($id_marketing);

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
        $request->validate(
            [
                'nama_marketing' => 'required|string',
                'alamat'         => 'required|string|max:50',
                'jenis_kelamin'  => 'required',
                'pekerjaan'      => 'nullable',
                'no_telp'        => 'required',
                'foto'           => 'nullable|mimes:jpg,jpeg,png|max:2048',
                'status'         => 'required', 
            ],
            [
                'nama_marketing.required' => 'Nama marketing wajib diisi.',
                'nama_marketing.string'   => 'Nama marketing harus berupa teks.',

                'alamat.required' => 'Alamat wajib diisi.',
                'alamat.string'   => 'Alamat harus berupa teks.',
                'alamat.max'      => 'Alamat tidak boleh lebih dari 50 karakter.',

                'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
                'jenis_kelamin.max'      => 'Jenis kelamin tidak valid.',

                'no_telp.required' => 'Nomor telepon wajib diisi.',

                'foto.mimes' => 'Foto harus berformat jpg, jpeg, atau png.',
                'foto.max'   => 'Ukuran foto maksimal 2MB.',

                'status.required' => 'Status wajib dipilih.',
            ]
        );

        $data = Marketing::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $fileName = $data->foto;

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $ext = strtolower($file->getClientOriginalExtension());

                if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format gambar tidak valid.'
                    ], 400);
                }

                $destinationPath = public_path('photo');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $newName = time() . '_' . uniqid() . '.webp';
                $fullPath = $destinationPath . '/' . $newName;
                $image = null;

                if ($ext === 'jpg' || $ext === 'jpeg') {
                    $image = imagecreatefromjpeg($file->getRealPath());
                } elseif ($ext === 'png') {
                    $image = imagecreatefrompng($file->getRealPath());
                }

                if ($image) {
                    if (imagewebp($image, $fullPath, 80)) {
                        if ($fileName && file_exists(public_path('photo/' . $fileName))) {
                            unlink(public_path('photo/' . $fileName));
                        }

                        imagedestroy($image);
                        $fileName = $newName;
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal menyimpan gambar WebP.'
                        ], 500);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal memproses gambar.'
                    ], 500);
                }
            }

            $data->update([
                'nama_marketing' => $request->nama_marketing,
                'alamat'         => $request->alamat,
                'jenis_kelamin'  => $request->jenis_kelamin,
                'pekerjaan'      => $request->pekerjaan ?? '',
                'no_telp'        => $request->no_telp,
                'foto'           => $fileName,
                'status'         => $request->status,
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

    public function destroy($id_marketing)
    {
        $user = Marketing::find($id_marketing);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        DB::beginTransaction();
        try {
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => true,
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage()
            ]);
        }
    }
}
