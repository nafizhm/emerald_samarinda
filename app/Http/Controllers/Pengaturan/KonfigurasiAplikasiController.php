<?php
namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\KonfigurasiAplikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KonfigurasiAplikasiController extends Controller
{
    public function index()
    {
        $data        = KonfigurasiAplikasi::first();
        $permissions = HakAksesController::getUserPermissions();
        $media = \App\Models\KonfigurasiMedia::orderBy('urutan')->get();
        $mediaPermissions = KonfigurasiMediaController::permissions();

        return view('admin.pengaturan.pengaturan_profil.index', compact('data', 'permissions', 'media', 'mediaPermissions'));
    }

    public function update(Request $request)
    {
        $id          = $request->input('id');
        $konfigurasi = KonfigurasiAplikasi::find($id);

        if (! $konfigurasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $rules = [
            'nama_perusahaan'   => 'string|max:255',
            'alamat'            => 'string|max:255',
            'email'             => 'email',
            'telp'              => 'min:12|max:13',
            'hape'              => 'min:12|max:13',
            'fax'               => 'string|max:20',
            'npwp_perusahaan'   => 'nullable|string|max:20',
            'pesan_jatuh_tempo' => 'nullable|string',
        ];

        $messages = [
            'nama_perusahaan.'           => 'Nama Perusahaan wajib diisi.',
            'nama_perusahaan.string'     => 'Nama Perusahaan harus berupa teks.',
            'nama_perusahaan.max'        => 'Nama Perusahaan tidak boleh lebih dari 255 karakter.',

            'alamat.string'              => 'Alamat harus berupa teks.',
            'alamat.max'                 => 'Alamat tidak boleh lebih dari 255 karakter.',

            'email.email'                => 'Email harus berupa alamat email yang valid.',

            'telp.min'                   => 'Nomor Telepon tidak boleh kurang dari 12 karakter.',
            'telp.max'                   => 'Nomor Telepon tidak boleh lebih dari 13 karakter.',

            'hape.min'                   => 'Nomor Handphone tidak boleh kurang dari 12 karakter.',
            'hape.max'                   => 'Nomor Handphone tidak boleh lebih dari 13 karakter.',

            'fax.string'                 => 'Nomor Fax harus berupa teks.',
            'fax.max'                    => 'Nomor Fax tidak boleh lebih dari 20 karakter.',

            'npwp_perusahaan.nullable'   => 'NPWP Perusahaan bersifat opsional.',
            'npwp_perusahaan.string'     => 'NPWP Perusahaan harus berupa teks.',
            'npwp_perusahaan.max'        => 'NPWP Perusahaan tidak boleh lebih dari 20 karakter.',

            'pesan_jatuh_tempo.nullable' => 'Pesan Jatuh Tempo bersifat opsional.',
            'pesan_jatuh_tempo.string'   => 'Pesan Jatuh Tempo harus berupa teks.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            $db = [
                'nama_perusahaan'   => $request->nama_perusahaan,
                'alamat'            => $request->alamat,
                'email'             => $request->email,
                'telp'              => $request->telp,
                'hape'              => $request->hape,
                'npwp_perusahaan'   => $request->npwp_perusahaan ?? '',
                'front_page'        => $request->front_page ?? 0,
                'pesan_jatuh_tempo' => $request->pesan_jatuh_tempo ?? '',
            ];

            $konfigurasi->update($db);

            $path = base_path('.env');
            if (file_exists($path)) {
                file_put_contents($path, preg_replace(
                    '/^APP_NAME=.*/m',
                    'APP_NAME="' . $request->nama_perusahaan . '"',
                    file_get_contents($path)
                ));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'message' => 'Terjadi Kesalahan saat memperbarui data!',
            ], 500);
        }
    }
}
