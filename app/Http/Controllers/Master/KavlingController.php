<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\FotoKavling;
use App\Models\KavlingPeta;
use App\Models\LokasiKavling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use TCPDF;
use Yajra\DataTables\Facades\DataTables;

class KavlingController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        $lokasiList = LokasiKavling::all();

        if ($request->ajax()) {
            $data = KavlingPeta::with('lokasi')
                ->when($request->id_lokasi, function ($query) use ($request) {
                    $query->where('id_lokasi', $request->id_lokasi);
                })
                ->orderBy('id', 'asc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('panjang', fn ($row) => $row->panjang . ' m')
                ->editColumn('lebar', fn ($row) => $row->lebar . ' m')
                ->addColumn('luas', function ($row) {
                    return '
                <p>luas tanah: <strong>' . $row->luas_tanah . ' m&sup2;</strong></p>
            ';
                })
                ->editColumn('harga', function ($row) {
                    return '
                <div class="d-flex justify-content-between harga-format w-100">
                    <span>Rp.</span>
                    <span>' . number_format($row->hrg_jual, 0, ',', '.') . '</span>
                </div>';
                })
                ->addColumn('action', function ($row) use ($permissions) {
                    $btn = '<div class="kavling-row-actions">';

                    if ($permissions['edit']) {
                        $btn .= '<a href="' . route('kavling.edit', $row->id) . '" class="edit-kavling btn btn-outline-primary btn-sm">Edit</a>';
                    }

                    $btn .= '<a href="' . route('kavling.lampiran', $row->id) . '" class="btn btn-success btn-sm">Lampiran</a>';

                    return $btn . '</div>';
                })
                ->rawColumns(['panjang', 'lebar', 'luas', 'harga', 'action'])
                ->make(true);
        }

        return view('admin.master.kavling.index', compact('permissions', 'lokasiList'));
    }

    public function edit(Request $request, $id)
    {
        $data = KavlingPeta::with('lokasi')->findOrFail($id);
        abort_unless(HakAksesController::getUserPermissions('kavling.index')['edit'], 403);
        if (! $request->expectsJson()) {
            return redirect()->route('kavling.index', ['edit' => $data->id]);
        }
        return response()->json(['data' => $data, 'project_count' => KavlingPeta::where('id_lokasi', $data->id_lokasi)->count()]);
    }

    public function update(Request $request, $id)
    {
        abort_unless(HakAksesController::getUserPermissions('kavling.index')['edit'], 403);
        $data = KavlingPeta::findOrFail($id);
        $request->merge([
            'hrg_jual' => str_replace('.', '', (string) $request->hrg_jual),
        ]);
        $values = $request->validate([
            'panjang' => 'required|numeric|min:0|max:9999999999.9|decimal:0,1',
            'lebar' => 'required|numeric|min:0|max:9999999999.9|decimal:0,1',
            'luas_tanah' => 'required|integer|min:0|max:2147483647',
            'hrg_jual' => 'required|integer|min:0|max:2147483647',
            'keterangan' => 'nullable|string|max:5000',
            'update_project' => 'sometimes|boolean',
        ]);
        unset($values['update_project']);
        $values['keterangan'] = $values['keterangan'] ?? '';
        if ($request->boolean('update_project') && ! $data->id_lokasi) {
            return response()->json(['success' => false, 'message' => 'Kavling belum memiliki proyek.'], 422);
        }
        try {
            $count = DB::transaction(function () use ($request, $data, $values) {
                $query = KavlingPeta::query();
                if ($request->boolean('update_project')) {
                    $query->where('id_lokasi', $data->id_lokasi);
                } else {
                    $query->whereKey($data->id);
                }
                $count = (clone $query)->count();
                $query->update($values);
                return $count;
            });
            return response()->json([
                'success' => true,
                'updated_count' => $count,
                'message' => $request->boolean('update_project')
                    ? "Data {$count} kavling dalam proyek ini berhasil diperbarui."
                    : 'Data kavling berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Data gagal disimpan. Silakan coba kembali.'], 500);
        }
    }

    public function lampiran($id)
    {
        $kavling = KavlingPeta::with('lokasi')->findOrFail($id);

        if (! $kavling) {
            return redirect()->route('kavling.index')->with('error', 'Data kavling tidak ditemukan.');
        }

        $fotos = FotoKavling::where('id_kavling', $id)->get();

        return view('admin.master.kavling.lampiran', compact('kavling', 'fotos'));
    }

    public function uploadLampiran(Request $request)
    {
        $id = $request->input('id');

        $request->validate([
            'tanggal'    => 'required|date',
            'nama_file'  => 'required|string|max:255',
            'lampiran'   => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'keterangan' => 'required|string|max:500',
        ], [
            'tanggal.required'    => 'Tanggal upload wajib diisi.',
            'keterangan.required' => 'keterangan wajib diisi.',
            'tanggal.date'        => 'Tanggal upload tidak valid.',
            'nama_file.required'  => 'Nama file wajib diisi.',
            'nama_file.string'    => 'Nama file harus berupa teks.',
            'nama_file.max'       => 'Nama file maksimal 255 karakter.',
            'lampiran.required'   => 'File wajib diunggah.',
            'lampiran.file'       => 'Lampiran harus berupa file.',
            'lampiran.mimes'      => 'File harus berupa JPG, JPEG, PNG, atau PDF.',
            'lampiran.max'        => 'Ukuran file maksimal 5MB.',
            'keterangan.string'   => 'Keterangan harus berupa teks.',
            'keterangan.max'      => 'Keterangan maksimal 500 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $file     = $request->file('lampiran');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('foto_kavling'), $filename);

            FotoKavling::create([
                'tanggal'    => $request->tanggal,
                'id_kavling' => $id,
                'file_name'  => $request->nama_file,
                'lampiran'   => $filename,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lampiran berhasil diunggah.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'message' => 'Terjadi kesalahan saat mengunggah lampiran.',
            ]);
        }
    }

    public function destroyLampiran($id)
    {
        $lampiran = FotoKavling::find($id);

        if (! $lampiran) {
            return response()->json([
                'success' => false,
                'id'      => $id,
                'message' => 'Lampiran tidak ditemukan.',
            ], 404);
        }

        try {
            if (File::exists(public_path('foto_kavling/' . $lampiran->lampiran))) {
                File::delete(public_path('foto_kavling/' . $lampiran->lampiran));
            }

            $lampiran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lampiran berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'message' => 'Terjadi kesalahan saat menghapus lampiran.',
            ], 500);
        }
    }

    public function cetakPdf(Request $request, $id_lokasi)
    {
        $query = KavlingPeta::with('lokasi');

        if ($id_lokasi && $id_lokasi != 0) {
            $query->where('id_lokasi', $id_lokasi);
        }

        $data = $query->get();

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Rhabayu Property');
        $pdf->SetAuthor('Rhabayu Property');
        $pdf->SetTitle('Data Kavling');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->AddPage();

        $namaLokasi = 'Semua Lokasi';
        if ($id_lokasi && $id_lokasi != 0) {
            $lokasiData = LokasiKavling::find($id_lokasi);
            $namaLokasi = $lokasiData ? $lokasiData->nama_kavling : 'Semua Lokasi';
        }

        // Industrial Formal Header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'DATA KAVLING ' . strtoupper($namaLokasi), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Dicetak pada tanggal: ' . date('d F Y'), 0, 1, 'C');
        $pdf->Ln(5);

        // Configure Table
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(240, 240, 240); // Light gray for industrial look
        $pdf->SetLineWidth(0.1);

        // Column Widths
        // Column Widths
        $w = [10, 30, 25, 35, 35, 35, 35, 40, 45];

        // Headers
        // Headers
        $headers = ['No', 'Kode Kavling', 'Luas Tanah', 'Harga Rumah', 'Biaya Surat', 'Pening. Mutu', 'Kelebihan Tanah', 'Adm. Posisi', 'Total Harga'];

        $pdf->Cell($w[0], 10, $headers[0], 1, 0, 'C', true);
        $pdf->Cell($w[1], 10, $headers[1], 1, 0, 'C', true);
        $pdf->Cell($w[2], 10, $headers[2], 1, 0, 'C', true);
        $pdf->Cell($w[3], 10, $headers[3], 1, 0, 'C', true);
        $pdf->Cell($w[4], 10, $headers[4], 1, 0, 'C', true);
        $pdf->Cell($w[5], 10, $headers[5], 1, 0, 'C', true);
        $pdf->Cell($w[6], 10, $headers[6], 1, 0, 'C', true);
        $pdf->Cell($w[7], 10, $headers[7], 1, 0, 'C', true);
        $pdf->Cell($w[8], 10, $headers[8], 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $no = 1;

        foreach ($data as $row) {
            $total = $row->hrg_jual + $row->biaya_surat + $row->peningkatan_mutu + $row->biaya_kelebihan_tanah + $row->admin_posisi_rumah;

            $pdf->Cell($w[0], 8, $no++, 1, 0, 'C');
            $pdf->Cell($w[1], 8, $row->kode_kavling ?? '-', 1, 0, 'C');
            $pdf->Cell($w[2], 8, $row->luas_tanah . ' m²', 1, 0, 'C');

            // Format prices with Rp. prefix and right alignment
            $pdf->Cell($w[3], 8, 'Rp. ' . number_format($row->hrg_jual, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($w[4], 8, 'Rp. ' . number_format($row->biaya_surat, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($w[5], 8, 'Rp. ' . number_format($row->peningkatan_mutu, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($w[6], 8, 'Rp. ' . number_format($row->biaya_kelebihan_tanah, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($w[7], 8, 'Rp. ' . number_format($row->admin_posisi_rumah, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($w[8], 8, 'Rp. ' . number_format($total, 0, ',', '.'), 1, 1, 'R');
        }

        $filename = 'Data Kavling ' . Str::slug($namaLokasi) . '.pdf';
        $pdf->Output($filename, 'I');
    }

    public function cetakExcel(Request $request, $id_lokasi = 0)
    {
        $query = KavlingPeta::with('lokasi');

        if ($id_lokasi && $id_lokasi != 0) {
            $query->where('id_lokasi', $id_lokasi);
        }

        $data = $query->get();

        $spreadsheet = new Spreadsheet;
        $sheet       = $spreadsheet->getActiveSheet();

        $namaLokasi = 'Semua Lokasi';
        if ($id_lokasi && $id_lokasi != 0) {
            $lokasiData = LokasiKavling::find($id_lokasi);
            $namaLokasi = $lokasiData ? $lokasiData->nama_kavling : 'Semua Lokasi';
        }

        $sheet->setTitle('Data Kavling');

        // Main Header
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'DATA KAVLING ' . strtoupper($namaLokasi));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setName('Arial');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Date Header
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Dicetak pada: ' . date('d F Y'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->setName('Arial');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'No',
            'Kode Kavling',
            'Luas Tanah',
            'Harga Rumah',
            'Biaya Surat',
            'Peringkatan Mutu',
            'Biaya Kelebihan Tanah',
            'Admin Posisi Rumah',
            'Total Harga',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $sheet->getStyle($col . '4')->getFont()->setBold(true)->setName('Arial');
            $sheet->getStyle($col . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . '4')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0'); // Light Gray Background
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $rowNum = 5;
        $no     = 1;
        foreach ($data as $row) {
            $total = $row->hrg_jual + $row->biaya_surat + $row->peningkatan_mutu + $row->biaya_kelebihan_tanah + $row->admin_posisi_rumah;

            $sheet->setCellValue("A{$rowNum}", $no++);
            $sheet->setCellValue("B{$rowNum}", $row->kode_kavling ?? '-');
            $sheet->setCellValue("C{$rowNum}", $row->luas_tanah . ' m²');
            $sheet->setCellValue("D{$rowNum}", $row->hrg_jual);
            $sheet->setCellValue("E{$rowNum}", $row->biaya_surat);
            $sheet->setCellValue("F{$rowNum}", $row->peningkatan_mutu);
            $sheet->setCellValue("G{$rowNum}", $row->biaya_kelebihan_tanah);
            $sheet->setCellValue("H{$rowNum}", $row->admin_posisi_rumah);
            $sheet->setCellValue("I{$rowNum}", $total);

            $sheet->getStyle("A{$rowNum}:I{$rowNum}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$rowNum}:C{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Format number columns (Currency Accounting-like format without symbol in Excel often prefer standard number)
            $sheet->getStyle("D{$rowNum}:I{$rowNum}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');

            $rowNum++;
        }

        // Borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A4:I' . ($rowNum - 1))->applyFromArray($styleArray);

        // Filename
        $fileName = 'Data Kavling ' . Str::slug($namaLokasi) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
