<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\KonfigurasiMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class KonfigurasiMediaController extends Controller
{
    public static function permissions(): array
    {
        $grants = DB::table('hak_akses')->join('menu', 'menu.id', '=', 'hak_akses.id_menu')
            ->where('hak_akses.id_user', Auth::id())
            ->whereIn('menu.route_name', ['pengaturan-profile.index', 'pengaturan-media.index'])
            ->get(['hak_akses.edit', 'hak_akses.hapus']);
        // Removing a file edits the media slot; the slot itself remains available.
        return ['edit' => (int) $grants->max('edit'), 'hapus' => max((int) $grants->max('edit'), (int) $grants->max('hapus'))];
    }

    public function index()
    {
        return redirect()->route('pengaturan-profile.index', ['tab' => 'media']);
    }

    public function edit($id)
    {
        return response()->json(['success' => true, 'data' => KonfigurasiMedia::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        abort_unless(self::permissions()['edit'], 403);
        $media = KonfigurasiMedia::findOrFail($id);
        $request->validate(['nama_file' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072'], [
            'nama_file.required' => 'Pilih gambar terlebih dahulu.',
            'nama_file.image' => 'File harus berupa gambar.',
            'nama_file.mimes' => 'Gunakan gambar JPG, PNG, atau WebP.',
            'nama_file.max' => 'Ukuran gambar maksimal 3 MB.',
        ]);
        $file = $request->file('nama_file');
        $name = Str::slug($media->jenis_data).'_'.Str::uuid().'.'.$file->extension();
        $directory = public_path('config_media');
        File::ensureDirectoryExists($directory);
        $old = $media->nama_file;
        try {
            $file->move($directory, $name);
            $media->update(['nama_file' => $name]);
        } catch (\Throwable $exception) {
            File::delete($directory.'/'.$name);
            report($exception);
            return response()->json(['success' => false, 'message' => 'Gambar gagal disimpan. Silakan coba kembali.'], 500);
        }
        $this->removeUnusedFile($old);
        return response()->json(['success' => true, 'message' => 'Media berhasil disimpan.', 'name' => $name, 'url' => asset('config_media/'.$name)]);
    }

    public function destroy($id)
    {
        abort_unless(self::permissions()['hapus'], 403);
        $media = KonfigurasiMedia::findOrFail($id);
        $old = $media->nama_file;
        $media->update(['nama_file' => '']);
        $this->removeUnusedFile($old);
        return response()->json(['success' => true, 'message' => 'File media dihapus. Anda dapat mengunggah gambar baru.']);
    }

    private function removeUnusedFile(?string $name): void
    {
        if ($name && basename($name) === $name && !KonfigurasiMedia::where('nama_file', $name)->exists()) {
            File::delete(public_path('config_media/'.$name));
        }
    }
}
