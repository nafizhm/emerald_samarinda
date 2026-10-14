<?php

namespace App\Http\Controllers;

use App\Services\DenahSqlImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class UploadDenahController extends Controller
{
    public function index()
    {
        return view('admin.upload_denah');
    }

    public function store(Request $request, DenahSqlImporter $importer)
    {
        $request->validate([
            'sql_file' => ['required', 'file', 'extensions:sql', 'max:20480'],
            'reset' => ['nullable', 'boolean'],
        ]);
        try {
            $counts = $importer->import(file_get_contents($request->file('sql_file')->getRealPath()), $request->boolean('reset'));
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['sql_file' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::error('Upload denah gagal', ['exception' => $exception]);
            return back()->withInput()->withErrors(['sql_file' => 'Import gagal. Seluruh perubahan dibatalkan. Periksa struktur dan relasi data SQL; detail tersedia di log aplikasi.']);
        }
        $summary = collect($counts)->map(fn ($count, $table) => "{$table}: {$count} baris")->implode(', ');
        return redirect()->route('uploaddenah.index')->with('success', 'Denah berhasil diimport. '.$summary);
    }
}
