<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\KavlingPeta;
use App\Models\LokasiKavling;
use App\Models\MasterSVG;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\DataTables;

class LokasiKavlingController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();
        $data        = LokasiKavling::all();

        if ($request->ajax()) {
            $LokasiKavling = LokasiKavling::withCount('kavlingPeta')
                ->orderBy('urutan', 'asc');

            return DataTables::of($LokasiKavling)
                ->addIndexColumn()
                ->addColumn('jumlah_kavling', function ($row) {
                    return $row->kavling_peta_count;
                })
                ->addColumn('detail', function ($row) {
                    $btn = '<a href="' . route('lokasi-kavling.detail', $row->id) . '" class="btn btn-sm btn-warning text-white">Detail Kavling</a>';

                    return $btn;
                })
                ->addColumn('action', function ($row) use ($permissions) {
                    $editUrl   = route('lokasi-kavling.edit', $row->id);
                    $deleteUrl = route('lokasi-kavling.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';
                    if ($permissions['edit'] && $permissions['edit'] == 1) {
                        $btn .= '<button class="btn btn-primary btn-sm me-1 edit-button"
                                data-id="' . e($row->id) . '"
                                data-url="' . e($editUrl) . '">Edit</button>';
                    }

                    if ($permissions['hapus'] && $permissions['hapus'] == 1) {
                        $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="delete-button btn btn-danger btn-sm">
                        Hapus
                    </button>
                    </form>';
                    }
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['jumlah_kavling', 'detail', 'action'])
                ->make(true);
        }

        return view('admin.master.lokasi_kavling.index', compact('permissions', 'data'));
    }

    public function detail($id)
    {
        $permissions = HakAksesController::getUserPermissions();

        if (request()->ajax()) {
            $kavlings = KavlingPeta::with(['customer.progres'])
                ->where('id_lokasi', $id)
                ->orderByRaw("SUBSTRING_INDEX(kode_kavling, '-', 1) ASC")
                ->orderByRaw("CAST(SUBSTRING_INDEX(kode_kavling, '-', -1) AS UNSIGNED) ASC")
                ->get();

            return DataTables::of($kavlings)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('lokasi-kavling.editDetail', $row->id);

                    $btn  = '<div class="d-flex justify-content-center">';
                    $btn .= '<button class="btn btn-primary btn-sm mx-1 edit-button"
                                data-id="' . e($row->id) . '"
                                data-url="' . e($editUrl) . '">Edit Detail</button>';
                    $btn .= '</div>';

                    return $btn;
                })
                ->addColumn('status_progres', function ($row) {
                    $customer = $row->customer->first();
                    if ($customer && $customer->progres) {
                        return [
                            'name'            => $customer->progres->status_progres,
                            'warna'           => $customer->progres->warna,
                            'warna_bootstrap' => $customer->progres->warna_bootstrap,
                        ];
                    }
                    return [
                        'name'            => 'Ready',
                        'warna'           => '#80ff00',
                        'warna_bootstrap' => 'success',
                    ];
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $lokasi       = LokasiKavling::where('id', $id)->first();
        $nama_kavling = $lokasi->nama_kavling;

        return view('admin.master.lokasi_kavling.detail', compact('permissions', 'id', 'nama_kavling'));
    }

    public function editDetail($id)
    {
        $data = KavlingPeta::find($id);
        if (! $data) {
            return response()->json([
                'success' => false,
                'message' => 'Data kavling tidak ditemukan.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], 200);
    }

    public function updateDetail(Request $request, $id)
    {
        $request->merge([
            'hrg_jual'       => str_replace('.', '', $request->hrg_jual),
            'panjang_kanan'  => str_replace(',', '.', $request->panjang_kanan),
            'panjang_kiri'   => str_replace(',', '.', $request->panjang_kiri),
            'lebar_depan'    => str_replace(',', '.', $request->lebar_depan),
            'lebar_belakang' => str_replace(',', '.', $request->lebar_belakang),
            'luas_tanah'     => str_replace(',', '.', $request->luas_tanah),
        ]);

        $request->validate([
            'panjang_kanan'  => 'required|numeric',
            'panjang_kiri'   => 'required|numeric',
            'lebar_depan'    => 'required|numeric',
            'lebar_belakang' => 'required|numeric',
            'luas_tanah'     => 'required|numeric',
            'hrg_meter'      => 'required|numeric',
            'hrg_jual'       => 'required|numeric',
            'keterangan'     => 'required|string|max:255',
        ], [
            'panjang_kanan.required'  => 'Panjang kanan wajib diisi.',
            'panjang_kanan.numeric'   => 'Panjang kanan harus berupa angka.',
            'panjang_kiri.required'   => 'Panjang kiri wajib diisi.',
            'panjang_kiri.numeric'    => 'Panjang kiri harus berupa angka.',
            'lebar_depan.required'    => 'Lebar depan wajib diisi.',
            'lebar_depan.numeric'     => 'Lebar depan harus berupa angka.',
            'lebar_belakang.required' => 'Lebar belakang wajib diisi.',
            'lebar_belakang.numeric'  => 'Lebar belakang harus berupa angka.',
            'luas_tanah.required'     => 'Luas tanah wajib diisi.',
            'luas_tanah.numeric'      => 'Luas tanah harus berupa angka.',
            'hrg_meter.required'      => 'Harga per meter wajib diisi.',
            'hrg_meter.numeric'       => 'Harga per meter harus berupa angka.',
            'hrg_jual.required'       => 'Harga jual wajib diisi.',
            'hrg_jual.numeric'        => 'Harga jual harus berupa angka.',
            'keterangan.required'     => 'Keterangan wajib diisi.',
            'keterangan.string'       => 'Keterangan harus berupa teks.',
            'keterangan.max'          => 'Keterangan maksimal 255 karakter.',
        ]);

        $data = KavlingPeta::findOrFail($id);

        $data->update([
            'panjang_kanan'  => $request->panjang_kanan,
            'panjang_kiri'   => $request->panjang_kiri,
            'lebar_depan'    => $request->lebar_depan,
            'lebar_belakang' => $request->lebar_belakang,
            'luas_tanah'     => $request->luas_tanah,
            'hrg_meter'      => $request->hrg_meter,
            'hrg_jual'       => $request->hrg_jual,
            'keterangan'     => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan',
        ]);
    }

    public function exportDetail($id)
    {
        try {
            $lokasi   = LokasiKavling::findOrFail($id);
            $kavlings = KavlingPeta::where('id_lokasi', $id)->get();

            $spreadsheet = new Spreadsheet;
            $sheet       = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('Sistem Kavling')
                ->setLastModifiedBy('Sistem Kavling')
                ->setTitle('Data Kavling - ' . $lokasi->nama_kavling)
                ->setSubject('Export Data Kavling')
                ->setDescription('Data kavling untuk lokasi ' . $lokasi->nama_kavling)
                ->setKeywords('kavling export data')
                ->setCategory('Reports');

            // Set sheet name
            $sheet->setTitle('Data Kavling');

            // Create header section
            $sheet->setCellValue('A1', 'LAPORAN DATA KAVLING');
            $sheet->mergeCells('A1:I1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Location info
            $sheet->setCellValue('A2', 'Lokasi: ' . $lokasi->nama_kavling);
            $sheet->mergeCells('A2:E2');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

            // Export date
            $sheet->setCellValue('F2', 'Tanggal Export: ' . date('d/m/Y H:i'));
            $sheet->mergeCells('F2:I2');
            $sheet->getStyle('F2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Add empty row
            $headerRow = 4;

            // Set column headers
            $headers = [
                'A' => 'No',
                'B' => 'Kode Kavling',
                'C' => 'Panjang Kanan (m)',
                'D' => 'Panjang Kiri (m)',
                'E' => 'Lebar Depan (m)',
                'F' => 'Lebar Belakang (m)',
                'G' => 'Luas Tanah (m²)',
                'H' => 'Harga Jual (Rp)',
                'I' => 'Keterangan',
            ];

            foreach ($headers as $col => $header) {
                $sheet->setCellValue($col . $headerRow, $header);
            }

            // Style header row
            $headerStyle = [
                'font'      => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill'      => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => '000000'],
                    ],
                ],
            ];

            $sheet->getStyle('A' . $headerRow . ':I' . $headerRow)->applyFromArray($headerStyle);

            // Set row height for header
            $sheet->getRowDimension($headerRow)->setRowHeight(25);

            // Fill data
            $dataStartRow = $headerRow + 1;
            $row          = $dataStartRow;
            $totalHarga   = 0;
            $totalLuas    = 0;

            foreach ($kavlings as $index => $kavling) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $kavling->kode_kavling ?? '-');
                $sheet->setCellValue('C' . $row, $kavling->panjang_kanan ?? 0);
                $sheet->setCellValue('D' . $row, $kavling->panjang_kiri ?? 0);
                $sheet->setCellValue('E' . $row, $kavling->lebar_depan ?? 0);
                $sheet->setCellValue('F' . $row, $kavling->lebar_belakang ?? 0);
                $sheet->setCellValue('G' . $row, $kavling->luas_tanah ?? 0);
                $sheet->setCellValue('H' . $row, $kavling->hrg_jual ?? 0);
                $sheet->setCellValue('I' . $row, $kavling->keterangan ?? '-');

                // Accumulate totals
                $totalLuas  += $kavling->luas_tanah ?? 0;
                $totalHarga += $kavling->hrg_jual ?? 0;

                $row++;
            }

            $lastDataRow = $row - 1;

            // Add summary row if there's data
            if ($kavlings->count() > 0) {
                $summaryRow = $row + 1;
                $sheet->setCellValue('A' . $summaryRow, 'TOTAL');
                $sheet->mergeCells('A' . $summaryRow . ':F' . $summaryRow);
                $sheet->setCellValue('G' . $summaryRow, $totalLuas);
                $sheet->setCellValue('H' . $summaryRow, $totalHarga);
                $sheet->setCellValue('I' . $summaryRow, $kavlings->count() . ' Unit');

                // Style summary row
                $summaryStyle = [
                    'font'      => ['bold' => true, 'size' => 11],
                    'fill'      => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2EFDA'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders'   => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => '000000'],
                        ],
                    ],
                ];

                $sheet->getStyle('A' . $summaryRow . ':I' . $summaryRow)->applyFromArray($summaryStyle);
                $lastDataRow = $summaryRow;
            }

            // Style data rows
            if ($kavlings->count() > 0) {
                // Zebra striping for data rows
                for ($i = $dataStartRow; $i <= $lastDataRow - 2; $i++) {
                    if (($i - $dataStartRow) % 2 == 0) {
                        $sheet->getStyle('A' . $i . ':I' . $i)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8F9FA');
                    }
                }

                // Add borders to all data
                $sheet->getStyle('A' . $dataStartRow . ':I' . $lastDataRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Format currency columns
                $sheet->getStyle('H' . $dataStartRow . ':H' . $lastDataRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Format number columns
                $sheet->getStyle('C' . $dataStartRow . ':G' . $lastDataRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }

            // Auto-size columns
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
            foreach ($columns as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Set minimum column widths
            $minWidths = [
                'A' => 5,  // No
                'B' => 15, // Kode Kavling
                'C' => 12, // Panjang Kanan
                'D' => 12, // Panjang Kiri
                'E' => 12, // Lebar Depan
                'F' => 12, // Lebar Belakang
                'G' => 12, // Luas Tanah
                'H' => 15, // Harga Jual
                'I' => 20, // Keterangan
            ];

            foreach ($minWidths as $col => $width) {
                if ($sheet->getColumnDimension($col)->getWidth() < $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }
            }

            // Center align number columns
            $sheet->getStyle('A' . $dataStartRow . ':A' . $lastDataRow)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $dataStartRow . ':H' . $lastDataRow)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Freeze panes at header row
            $sheet->freezePane('A' . ($headerRow + 1));

            // Create filename with timestamp
            $timestamp = date('Y-m-d_H-i-s');
            $filename  = 'Data_Kavling_' . Str::slug($lokasi->nama_kavling) . '_' . $timestamp . '.xlsx';

            // Write file
            $writer = new Xlsx($spreadsheet);

            // Set response headers
            $headers = [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma'              => 'no-cache',
                'Expires'             => '0',
            ];

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, $headers);
        } catch (\Exception $e) {
            // Log error
            Log::error('Excel export failed: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengexport data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls',
            'id_lokasi'  => 'required|exists:lokasi_kavling,id_lokasi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $file        = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();

            $rows = $sheet->toArray(null, true, false, false);

            foreach ($rows as $idx => $row) {
                if ($idx === 0) {
                    continue;
                }

                $row = array_pad($row, 22, '');

                $kode = trim($row[0]);
                if ($kode === '') {
                    continue;
                }

                KavlingPeta::updateOrCreate(
                    [
                        'kode_kavling' => $kode,
                        'id_lokasi'    => $request->id_lokasi,
                        'id_cluster'   => $request->id_lokasi,
                    ],
                    [
                        'panjang_kanan'      => is_numeric($row[1]) ? $row[1] : 0,
                        'panjang_kiri'       => is_numeric($row[2]) ? $row[2] : 0,
                        'lebar_depan'        => is_numeric($row[3]) ? $row[3] : 0,
                        'lebar_belakang'     => is_numeric($row[4]) ? $row[4] : 0,
                        'luas_tanah'         => is_numeric($row[5]) ? $row[5] : 0,
                        'hrg_meter'          => is_numeric($row[6]) ? $row[6] : 0,
                        'tipe_bangunan'      => $row[7] !== '' ? $row[7] : '-',
                        'daya_listrik'       => $row[8] !== '' ? $row[8] : '-',
                        'id_rumah_sikumbang' => $row[9] !== '' ? $row[9] : '-',
                        'no_sertifikat'      => $row[10] !== '' ? $row[10] : '-',
                        'jenis_map'          => $row[11] !== '' ? $row[11] : '-',
                        'map'                => $row[12] !== '' ? $row[12] : '-',
                        'success'            => is_numeric($row[13]) ? $row[13] : 0,
                        'atas_nama_surat'    => $row[14] !== '' ? $row[14] : '-',
                        'id_customer'        => is_numeric($row[15]) ? $row[15] : 0,
                        'tgl_jatuh_tempo'    => is_numeric($row[16]) ? $row[16] : 0,
                        'luas_bangunan'      => is_numeric($row[17]) ? $row[17] : 0,
                        'hrg_jual'           => is_numeric($row[18]) ? $row[18] : 0,
                        'keterangan'         => $row[19],
                        'matrik'             => $row[20] !== '' ? $row[20] : '-',
                        'stt_cicilan'        => is_numeric($row[21]) ? $row[21] : 0,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Data Excel berhasil diimpor!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function setting($id)
    {
        $permissions = HakAksesController::getUserPermissions();
        $lokasi      = LokasiKavling::findOrFail($id);
        $data        = MasterSVG::where('id_lokasi', $id)->first();

        return view('admin.master.lokasi_kavling.setting', compact('lokasi', 'data', 'permissions'));
    }

    public function updateSetting(Request $request, $id)
    {
        $request->validate([
            'header_xml'       => 'required|string',
            'header_svg'       => 'required|string',
            'polygon_svg'      => 'required|string',
            'path_svg'         => 'required|string',
            'footer_svg'       => 'required|string',
            'lebar'            => 'required|numeric',
            'tinggi'           => 'required|numeric',
            'ukuran_dashboard' => 'required|string',
        ], [
            'header_xml.required'       => 'Header XML wajib diisi.',
            'header_svg.required'       => 'Header SVG wajib diisi.',
            'polygon_svg.required'      => 'Polygon SVG wajib diisi.',
            'path_svg.required'         => 'Path SVG wajib diisi.',
            'footer_svg.required'       => 'Footer SVG wajib diisi.',
            'lebar.required'            => 'Lebar wajib diisi.',
            'tinggi.required'           => 'Tinggi wajib diisi.',
            'ukuran_dashboard.required' => 'Ukuran dashboard wajib diisi.',
        ]);

        $data = MasterSVG::where('id_lokasi', $id)->first();

        if (! $data) {
            return response()->json([
                'success' => false,
                'message' => 'Data lokasi kavling tidak ditemukan.',
            ]);
        }

        $data->update([
            'header_xml'       => $request->input('header_xml'),
            'header_svg'       => $request->input('header_svg'),
            'polygon_svg'      => $request->input('polygon_svg'),
            'path_svg'         => $request->input('path_svg'),
            'footer_svg'       => $request->input('footer_svg'),
            'lebar'            => $request->input('lebar'),
            'tinggi'           => $request->input('tinggi'),
            'ukuran_dashboard' => $request->input('ukuran_dashboard'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data lokasi kavling berhasil disimpan.',
        ]);
    }

    public function create()
    {

        return view('admin.master.lokasi_kavling.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kavling'          => 'required|string|max:100',
            'nama_singkat'          => 'required|string|max:15',
            'alamat'                => 'required|string|max:255',
            'urutan'                => 'required|integer',
            'nama_perusahaan'       => 'required|string|max:100',
            'alamat_perusahaan'     => 'required|string|max:255',
            'telp_perusahaan'       => 'required|string|max:20',
            'bg_kwitansi'           => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'kop_surat'             => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'foto_kavling'          => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'kota_penandatangan'    => 'required|string',
            'nama_penandatangan'    => 'required|string',
            'nama_admin'            => 'required|string',
            'nama_mengetahui'       => 'required|string',
            'jabatan_penandatangan' => 'required|string',
            'header'                => 'required|string',
            'stt_tampil'            => 'required',
        ], [
            'required' => ':attribute wajib diisi.',
            'string'   => ':attribute harus berupa teks.',
            'integer'  => ':attribute harus berupa angka.',
            'numeric'  => ':attribute harus berupa angka.',
            'max'      => ':attribute maksimal :max karakter/kilobyte.',
            'mimes'    => ':attribute harus berupa JPG, JPEG, PNG, atau PDF.',
        ]);

        if ($request->hasFile('bg_kwitansi')) {
            $file     = $request->file('bg_kwitansi');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

            $file->move(public_path('bg_kwitansi'), $filename);

            $validated['bg_kwitansi'] = $filename;
        }

        if ($request->hasFile('kop_surat')) {
            $file     = $request->file('kop_surat');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

            $file->move(public_path('kop_surat'), $filename);

            $validated['kop_surat'] = $filename;
        }

        if ($request->hasFile('foto_kavling')) {
            $file     = $request->file('foto_kavling');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

            $file->move(public_path('assets/homepage'), $filename);

            $validated['foto_kavling'] = $filename;
        }

        LokasiKavling::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data lokasi kavling berhasil disimpan.',
        ]);
    }

    public function edit($id)
    {
        $data = LokasiKavling::find($id);

        if (! $data) {
            return response()->json([
                'success' => false,
                'message' => 'Data lokasi kavling tidak ditemukan.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data lokasi kavling ditemukan.',
            'data'    => $data,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_kavling'          => 'required|string|max:100',
            'nama_singkat'          => 'required|string|max:15',
            'alamat'                => 'required|string|max:255',
            'urutan'                => 'required|integer',
            'nama_perusahaan'       => 'required|string|max:100',
            'alamat_perusahaan'     => 'required|string|max:255',
            'telp_perusahaan'       => 'required|string|max:20',
            'bg_kwitansi'           => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'kop_surat'             => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'foto_kavling'          => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'kota_penandatangan'    => 'required|string',
            'nama_penandatangan'    => 'required|string',
            'nama_admin'            => 'required|string',
            'nama_mengetahui'       => 'required|string',
            'jabatan_penandatangan' => 'required|string',
            'header'                => 'required|string',
            'stt_tampil'            => 'required',
        ], [
            'required' => ':attribute wajib diisi.',
            'string'   => ':attribute harus berupa teks.',
            'integer'  => ':attribute harus berupa angka.',
            'numeric'  => ':attribute harus berupa angka.',
            'max'      => ':attribute maksimal :max karakter/kilobyte.',
            'mimes'    => ':attribute harus berupa JPG, JPEG, PNG, atau PDF.',
        ]);

        $data = LokasiKavling::findOrFail($id);

        if ($request->hasFile('bg_kwitansi')) {
            $oldPath = public_path('bg_kwitansi/' . $data->bg_kwitansi);
            if ($data->bg_kwitansi && file_exists($oldPath)) {
                unlink($oldPath);
            }

            $file     = $request->file('bg_kwitansi');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('bg_kwitansi'), $filename);

            $validated['bg_kwitansi'] = $filename;
        } else {
            unset($validated['bg_kwitansi']);
        }

        if ($request->hasFile('kop_surat')) {
            $oldPath = public_path('kop_surat/' . $data->kop_surat);
            if ($data->kop_surat && file_exists($oldPath)) {
                unlink($oldPath);
            }

            $file     = $request->file('kop_surat');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('kop_surat'), $filename);

            $validated['kop_surat'] = $filename;
        } else {
            unset($validated['kop_surat']);
        }
        if ($request->hasFile('foto_kavling')) {

            if ($data->foto_kavling) {
                $oldPath = public_path('assets/homepage/' . $data->foto_kavling);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $file     = $request->file('foto_kavling');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/homepage'), $filename);

            $validated['foto_kavling'] = $filename;
        }

        $data->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
        ]);
    }

    public function destroy($id_lokasi)
    {
        try {
            $lokasi = LokasiKavling::findOrFail($id_lokasi);
            $kavlingIds = DB::table('kavling_peta')->where('id_lokasi', $lokasi->id)->pluck('id');
            $customerIds = DB::table('customer')->where('id_lokasi', $lokasi->id)->pluck('id');
            if ($kavlingIds->isNotEmpty() || $customerIds->isNotEmpty()) {
                return response()->json(['success' => false, 'message' => 'Lokasi tidak dapat dihapus karena sudah memiliki kavling atau customer. Nonaktifkan lokasi agar laporan tetap aman.'], 422);
            }
            DB::transaction(function () use ($lokasi) {
                DB::table('master_svg')->where('id_lokasi', $lokasi->id)->delete();
                $lokasi->delete();
            });
            return response()->json(['success' => true, 'message' => 'Lokasi kosong berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Error menghapus lokasi kavling: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Data gagal dihapus.'], 500);
        }
    }
}