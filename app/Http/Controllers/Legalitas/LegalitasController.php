<?php

namespace App\Http\Controllers\Legalitas;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Customer;
use App\Models\KavlingPeta;
use App\Models\UploadFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LegalitasController extends Controller
{
    public function index(Request $request)
{
    $permissions = HakAksesController::getUserPermissions();

    if ($request->ajax()) {

        $data = KavlingPeta::with('customer.progres')
            ->orderBy('id', 'asc');

        if (filled($request->filter) && $request->filter == 'laku') {
            $data->whereHas('customer', function ($q) {
                $q->where('stt_arsip', 0);
            });
        }

        return DataTables::of($data)
            ->addIndexColumn()

            ->editColumn('kode_kavling', fn ($row) => $row->kode_kavling ?? '')

            ->addColumn('nama_konsumen', function ($row) {
                $customer = $row->customer->first();
                return $customer->nama_lengkap ?? '';
            })

            ->addColumn('atas_nama', function ($row) {
                $customer = $row->customer->first();
                return $customer->atas_nama ?? '';
            })

            ->addColumn('no_surat', function ($row) {
                $customer = $row->customer->first();
                return $customer->no_surat ?? '';
            })

            ->addColumn('progres', function ($row) {
                $customer = $row->customer->first();

                if (!$customer || !$customer->progres) {
                    return '';
                }

                return '<span class="badge text-sm text-white"
                    style="background-color:' . e($customer->progres->warna) . ';
                    padding:4px 8px; border-radius:6px;">'
                    . e($customer->progres->status_progres) .
                    '</span>';
            })

            ->addColumn('bukti_foto', function ($row) {
                $customer = $row->customer->first();

                if (!$customer) {
                    return '';
                }

                $fotoUrl = route('getFotoLegalitas', $customer->id);

                return '<div class="d-flex justify-content-center">
                    <button class="btn btn-info btn-sm mx-1 lihat-button"
                        data-id="' . e($customer->id) . '"
                        data-url="' . e($fotoUrl) . '"
                        data-bs-toggle="modal"
                        data-bs-target="#modalFoto">
                        Lihat
                    </button>
                </div>';
            })

            ->addColumn('keterangan', function ($row) {
                $customer = $row->customer->first();
                return $customer->keterangan_legalitas ?? '';
            })

            ->addColumn('action', function ($row) {
                $customer = $row->customer->first();

                if (!$customer) {
                    return '';
                }

                $editUrl = route('legalitas.edit', $customer->id);

                return '<div class="d-flex justify-content-center">
                    <button class="btn btn-primary btn-sm mx-1 edit-button"
                        data-id="' . e($customer->id) . '"
                        data-url="' . e($editUrl) . '"
                        data-bs-toggle="modal"
                        data-bs-target="#modalForm">
                        Edit
                    </button>
                </div>';
            })

            ->rawColumns(['action', 'progres', 'bukti_foto'])
            ->make(true);
    }

    return view('admin.legalitas.index', compact('permissions'));
}


    public function edit($id)
    {
        $data = Customer::find($id);

        if ($data) {
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data ditemukan'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan'
        ], 404);
    }

    public function update(Request $request, $id)
    {
        $data = Customer::findOrFail($id);

        $request->validate([
            'atas_nama' => 'nullable|string|max:150',
            'no_surat' => 'nullable|string|max:255',
            'keterangan_legalitas' => 'nullable|string',
            'upload_foto.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120', // max 5MB
        ], [
            'atas_nama.string' => 'Input harus berupa teks.',
            'atas_nama.max' => 'Isi maksimal 255 karakter.',
            'no_surat.string' => 'Input harus berupa teks.',
            'no_surat.max' => 'Isi maksimal 255 karakter.',
            'keterangan_legalitas.string' => 'Input harus berupa teks.',
            'upload_foto.*.file' => 'File tidak valid.',
            'upload_foto.*.mimes' => 'Format file harus jpg, jpeg, png, webp, atau pdf.',
            'upload_foto.*.max' => 'Ukuran file maksimal 5MB.',
        ]);

        DB::beginTransaction();

        // simpan daftar file yang udah dipindah
        $movedFiles = [];

        try {
            // --- UPDATE DATA CUSTOMER ---
            $db = [
                'atas_nama' => $request->atas_nama,
                'no_surat' => $request->no_surat,
                'keterangan_legalitas' => $request->keterangan_legalitas,
            ];

            $data->update($db);

            // --- PROSES UPLOAD FILE ---
            if ($request->hasFile('upload_foto')) {
                $folder = 'berkas_user';
                $destination = public_path($folder);

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                foreach ($request->file('upload_foto') as $file) {
                    if ($file) {
                        $originalExtension = strtolower($file->getClientOriginalExtension());
                        $cleanName = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                        $finalName = time() . '_' . $cleanName . '.' . $originalExtension;

                        $file->move($destination, $finalName);

                        // catat file yang udah dipindah
                        $movedFiles[] = $destination . '/' . $finalName;

                        // INSERT KE TABEL UPLOAD_FILE (DINAMIS)
                        UploadFile::create([
                            'tanggal'     => now()->format('Y-m-d'),
                            'id_customer' => $data->id,
                            'folder'      => $folder,
                            'nama_file'   => $cleanName,
                            'keterangan'  => 'Lampiran legalitas',
                            'lampiran'    => $finalName,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui dan lampiran berhasil disimpan.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            // --- HAPUS FILE YANG UDAH TERLANJUR DIPINDAH ---
            foreach ($movedFiles as $filePath) {
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getFotoLegalitas($id)
    {
        Carbon::setLocale('id');
        try {
            $data = UploadFile::where('id_customer', $id)
                ->where('keterangan', 'Lampiran legalitas')
                ->orderBy('tanggal', 'desc')
                ->get(['id', 'folder', 'nama_file', 'lampiran', 'tanggal', 'keterangan']);

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada foto legalitas untuk customer ini.'
                ], 404);
            }

            $result = $data->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'nama_file'  => $item->nama_file,
                    'tanggal'    => Carbon::parse($item->tanggal)->translatedFormat('d F Y'),
                    'keterangan' => $item->keterangan,
                    'url'        => asset($item->folder . '/' . $item->lampiran),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data foto legalitas berhasil diambil.',
                'data'    => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
