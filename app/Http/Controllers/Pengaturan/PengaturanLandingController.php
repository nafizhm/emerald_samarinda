<?php
namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Konten;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use App\Models\LokasiKavling;
use App\Models\MasterSVG;
use App\Models\KavlingPeta;
use App\Models\Customer;
use App\Models\ListPenjualan;

class PengaturanLandingController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $opd = Konten::query();

            return DataTables::of($opd)
                ->addIndexColumn()
                ->addColumn('icon', function ($row) {
                    return $row->icon ? '<i class="' . e($row->icon) . '" style="font-size:14px;"></i>' : '';
                })

              ->addColumn('nama_file', function ($row) {

                    if (!$row->nama_file) {
                        return '';
                    }

                    $path = public_path('assets/konten/' . $row->nama_file);

                    if (!file_exists($path)) {
                        return '';
                    }

                    $url = asset('assets/konten/' . $row->nama_file);
                    $ext = strtolower(pathinfo($row->nama_file, PATHINFO_EXTENSION));

                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        return '<img src="' . $url . '" alt="Gambar" style="max-width:80px; max-height:60px;">';
                    }

                    return '<a href="' . $url . '" target="_blank" class="text-primary fw-bold">
                                <i class="bi bi-file-earmark"></i> ' . e($row->nama_file) . '
                            </a>';
                })


                ->editColumn('jenis_content', function ($row) {
                    $jenisContentList = [
                        1 => 'Slider',
                        2 => 'About Us',
                        3 => 'Produk',
                        4 => 'Document',
                        5 => 'Tertiary Content',
                        6 => 'Fasility',
                        7 => 'Footer',
                        8 => 'Navbar Item',
                        9 => 'Navbar Logo',
                        10 => 'Progres Pembangunan',
                        11 => 'Hero Section',
                    ];
                    return $jenisContentList[$row->jenis_content] ?? 'Tidak Diketahui';
                })

                ->addColumn('action', function ($row) use ($permissions) {
                    $editUrl   = route('pengaturanLanding.edit', $row->id);
                    $deleteUrl = route('pengaturanLanding.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';
                    if ($permissions['edit']) {
                        $btn .= '<button class="btn btn-primary btn-sm edit-button me-1" data-id="' . e($row->id) . '" data-url="' . e($editUrl) . '">Edit</button>';
                    }

                    if ($permissions['hapus']) {
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
                ->rawColumns(['action', 'icon', 'nama_file'])
                ->make(true);

        }

        return view('admin.pengaturan.pengaturan_landing.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $jenis = $request->jenis_content;

        $baseRules = [
            'jenis_content' => 'required|string|max:45',
        ];
        $messages = [
            'jenis_content.required' => 'Jenis Konten wajib diisi.',
            'jenis_content.string'   => 'Jenis Konten harus berupa teks.',
            'jenis_content.max'      => 'Jenis Konten tidak boleh lebih dari 45 karakter.',
        ];

        if (in_array($jenis, ['6'])) {
            $baseRules['judul']     = 'required|string|max:255';
            $baseRules['artikel']   = 'required|string';
            $baseRules['icon']      = 'required';
            $baseRules['nama_file'] = 'nullable|file';
            $messages += [
                'judul.required'   => 'Judul wajib diisi.',
                'artikel.required' => 'Artikel wajib diisi.',
                'icon.required'    => 'Icon wajib dipilih.',
                'nama_file.mimes'  => 'File harus berupa gambar (jpg, jpeg, png)',
                'nama_file.max'    => 'Ukuran file maksimal 2MB.',
            ];
        } elseif ($jenis == '8') {
            $baseRules['judul']     = 'required|string|max:255';
            $messages += [
                'judul.required'    => 'Judul wajib diisi.',
            ];
        } elseif ($jenis == '9') {
            $baseRules['artikel'] = 'nullable|string';
            $baseRules['judul'] = 'nullable|string';
            $baseRules['icon'] = 'nullable|string';
            $baseRules['url_item'] = 'nullable|string';
            $baseRules['nama_file'] = 'required|file';
            $messages += [
                'nama_file.required' => 'File Konten wajib diisi.',
                'nama_file.file'     => 'File yang diunggah tidak valid.',
                'nama_file.mimes'    => 'File harus berupa gambar (jpg, jpeg, png)',
                'nama_file.max'      => 'Ukuran file maksimal 2MB.',
            ];
        } elseif (in_array($jenis, ['4'])) {
            $baseRules['judul']    = 'required|string|max:255';
            $baseRules['nama_file'] = 'required|file';
            $messages += [
                'judul.required'    => 'judul Konten wajib diisi.',
                'nama_file.required' => 'File Konten wajib diisi.',
            ];
        } elseif (in_array($jenis, ['10'])) {
            $baseRules['judul']    = 'required|string|max:255';
            $baseRules['url_item'] = 'required|string';
            $baseRules['artikel']  = 'required|string';
            $messages += [
                'judul.required'    => 'judul Konten wajib diisi.',
                'url_item.required' => 'Url wajib diisi.',
                'artikel.required'  => 'Artikel wajib diisi.',
            ];
        } elseif (in_array($jenis, ['1'])) {
            $baseRules['nama_file'] = 'required|file';
            $messages += [
                'nama_file.mimes'    => 'File harus berupa gambar (jpg, jpeg, png)',
                'nama_file.max'      => 'Ukuran file maksimal 2MB.',
            ];
        } else {
            $baseRules['judul']     = 'required|string|max:255';
            $baseRules['url_item']  = 'nullable|string';
            $baseRules['artikel']   = 'required|string';
            $baseRules['icon']      = 'nullable';
            $baseRules['nama_file'] = 'required|file';
            $messages += [
                'judul.required'     => 'Judul wajib diisi.',
                'artikel.required'   => 'Artikel wajib diisi.',
                'nama_file.required' => 'File Konten wajib diisi.',
                'nama_file.mimes'    => 'File harus berupa gambar (jpg, jpeg, png) atau PDF.',
                'nama_file.max'      => 'Ukuran file maksimal 2MB.',
            ];
        }

        $request->validate($baseRules, $messages);

        DB::beginTransaction();
        try {
            $data = [
                'jenis_content' => $jenis,
                'judul'         => $request->judul,
                'url_item'      => $request->url_item,
                'artikel'       => $request->artikel,
                'icon'          => $request->icon,
                'tanggal'       => now(),
            ];

            $folder = public_path('assets/konten');
            if (! file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

          if ($request->hasFile('nama_file') && $request->file('nama_file')->isValid()) {

                $file = $request->file('nama_file');

                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = preg_replace('/[^a-zA-Z0-9-_]/', '_', $filename);
                $ext = strtolower($file->getClientOriginalExtension());

                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {

                    $filename .= '_' . time() . '.webp';
                    $outputPath = $folder . '/' . $filename;

                    if ($ext === 'png') {
                        $source = imagecreatefrompng($file->getPathname());
                    } else {
                        $source = imagecreatefromjpeg($file->getPathname());
                    }

                    if ($source) {
                        imagepalettetotruecolor($source);
                        imagewebp($source, $outputPath, 80);
                        imagedestroy($source);

                        $data['nama_file'] = $filename;
                    }

                } 
                else {

                    $filename .= '_' . time() . '.' . $ext;
                    $file->move($folder, $filename);

                    $data['nama_file'] = $filename;
                }
            }


            Konten::create($data);
            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return response()->json([
                'status' => 'error',
                'error'  => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $list = Konten::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ]);
    }

    public function update(Request $request, $id)
    {
        $konten = Konten::findOrFail($id);
        $jenis  = $request->jenis_content;

        $rules = [
            'jenis_content' => 'nullable|string|max:45',
        ];
        $messages = [
            'jenis_content.max'      => 'Jenis Konten tidak boleh lebih dari 45 karakter.',
        ];

        if (in_array($jenis, ['6'])) {
            $rules['judul']     = 'required|string|max:255';
            $rules['artikel']   = 'required|string';
            $rules['icon']      = 'required';
            $rules['nama_file'] = 'nullable|file';
            $messages += [
                'judul.required'   => 'Judul wajib diisi.',
                'artikel.required' => 'Artikel wajib diisi.',
                'icon.required'    => 'Icon wajib dipilih.',
            ];
        } elseif ($jenis == '8') {
            $rules['judul']     = 'required|string|max:255';
            $rules['url_item']  = 'required|string';
            $rules['nama_file'] = 'nullable|file';
            $messages += [
                'judul.required'    => 'Judul wajib diisi.',
                'url_item.required' => 'Url wajib diisi.',
            ];
        } elseif ($jenis == '9') {
            $rules['judul'] = 'nullable|string';
            $rules['artikel'] = 'nullable|string';
            $rules['icon'] = 'nullable|string';
            $rules['url_item'] = 'nullable|string';
            $rules['nama_file'] = 'required|file';
            $messages += [
                'nama_file.required' => 'File Konten wajib diisi.',
                'nama_file.file'  => 'File yang diunggah tidak valid.',
                'nama_file.mimes' => 'File harus berupa gambar (jpg, jpeg, png)',
            ];
        } elseif (in_array($jenis, ['4', '5'])) {
            $rules['judul']    = 'required|string|max:255';
            $rules['url_item'] = 'required|string';
            $rules['artikel']  = 'nullable|string';
            $messages += [
                'judul.required'    => 'judul Konten wajib diisi.',
                'url_item.required' => 'Url wajib diisi.',
                'artikel.required'  => 'Artikel wajib diisi.',
            ];
        } elseif (in_array($jenis, ['10'])) {
            $rules['judul']    = 'required|string|max:255';
            $rules['nama_file'] = 'required|string';
            $rules['url_item'] = 'nullable|string';
            $rules['artikel']  = 'required|string';
            $messages += [
                'judul.required'    => 'judul Konten wajib diisi.',
                'url_item.required' => 'Url wajib diisi.',
                'artikel.required'  => 'Artikel wajib diisi.',
                 'nama_file.required' => 'File Konten wajib diisi.',
                'nama_file.file'  => 'File yang diunggah tidak valid.',
                'nama_file.mimes' => 'File harus berupa gambar (jpg, jpeg, png)',
            ];
              } elseif (in_array($jenis, ['1', '11'])) {
            $baseRules['nama_file'] = 'required|file';
            $messages += [
                'nama_file.mimes'    => 'File harus berupa gambar (jpg, jpeg, png)',
                'nama_file.max'      => 'Ukuran file maksimal 2MB.',
            ];
        } else {
            $rules['judul']     = 'nullable|string|max:255';
            $rules['url_item']  = 'nullable|string';
            $rules['artikel']   = 'nullable|string';
            $rules['icon']      = 'nullable';
            $rules['nama_file'] = 'nullable|file';
            $messages += [
                'nama_file.mimes'   => 'File harus berupa gambar (jpg, jpeg, png)',
                'nama_file.max'     => 'Ukuran file maksimal 2MB.',
            ];
        }

        $request->validate($rules, $messages);

        $konten->judul         = $request->judul;
        $konten->url_item      = $request->url_item;
        $konten->artikel       = $request->artikel;
        $konten->icon          = $request->icon;

        $folder = public_path('assets/konten');
        if (! file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        if ($request->hasFile('nama_file') && $request->file('nama_file')->isValid()) {
            if ($konten->nama_file && file_exists($folder . '/' . $konten->nama_file)) {
                unlink($folder . '/' . $konten->nama_file);
            }

            $file     = $request->file('nama_file');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = preg_replace('/[^a-zA-Z0-9-_]/', '_', $filename);
            $ext      = strtolower($file->getClientOriginalExtension());

            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $filename .= '_' . time() . '.webp';
                $outputPath = $folder . '/' . $filename;
                if ($ext === 'png') {
                    $source = imagecreatefrompng($file->getPathname());
                } else {
                    $source = imagecreatefromjpeg($file->getPathname());
                }
                if ($source) {
                    imagepalettetotruecolor($source);
                    imagewebp($source, $outputPath, 80);
                    imagedestroy($source);
                    $konten->nama_file = $filename;
                }
            } else {
                $filename .= '.' . $ext;
                $file->move($folder, $filename);
                $konten->nama_file = $filename;
            }
        }

        $konten->save();

        return response()->json(['status' => 'success']);
    }

    public function homepage()
    {
        $sliders = Konten::where('jenis_content', 1)->whereNotNull('nama_file')->get();
        $aboutUs = Konten::where('jenis_content', 2)->first();
        $progres = Konten::where('jenis_content', 10)->first();
        
        $heroSection = Konten::where('jenis_content', 11)->first();
        
        $document = Konten::where('jenis_content', 4)->get();
        $tertiaryContent  = Konten::where('jenis_content', 5)->first();
        $facilities       = Konten::where('jenis_content', 6)->get();
        $footer   = Konten::where('jenis_content', 7)->get();
        $logo     = Konten::where('jenis_content', '9')->first();
        $navItems = Konten::where('jenis_content', '8')->get();

        $beranda   = Konten::where('jenis_content', 1)->first();
        $product   = Konten::where('jenis_content', 3)->get();

        $kantor = DB::table('konfigurasi')->first();

         $lokasiKavling = LokasiKavling::with('kavlingPeta')
            ->where('stt_tampil', 3) 
            ->orderBy('urutan', 'asc')
            ->get();

        return view('homepage.index', compact(
            'sliders',
            'aboutUs',
            'document',
            'tertiaryContent',
            'facilities',
            'footer',
            'navItems',
            'logo',
            'progres',
            'beranda',
            'product',
            'heroSection',
            'kantor',
            'lokasiKavling',
        ));
    }

   public function detailKavling($id)
    {
        $navItems = Konten::where('jenis_content', '8')->get();
        $footer   = Konten::where('jenis_content', 7)->get();
        $kantor = DB::table('konfigurasi')->first();
        $heroSection = Konten::where('jenis_content', 11)->first();

        $kav = LokasiKavling::with([
            'masterSvg',
            'kavlingPeta.customer' => function ($q) {
                $q->where('stt_arsip', 0);
            },
            'kavlingPeta.customer.progres',
        ])->findOrFail($id);

        $legend = ListPenjualan::whereNotNull('warna')
            ->where('warna', '!=', '')
            ->orderBy('urutan', 'asc')
            ->get();

        return view('homepage.kavling', compact('kav', 'legend',  'navItems', 'footer', 'kantor', 'heroSection',));
    }



    public function aboutus()
    {
        $aboutUs = Konten::where('jenis_content', 2)->first();


        $logo     = Konten::where('jenis_content', '9')->first();
        $footer   = Konten::where('jenis_content', 7)->get();
        $tertiaryContent  = Konten::where('jenis_content', 5)->first();


        return view('frontend.homepage.aboutUs', compact(
            'logo',
            'footer',
            'tertiaryContent',
            'aboutUs',
        ));
    }

    public function progres()
    {
        $progres = Konten::where('jenis_content', 10)->first();
        $logo     = Konten::where('jenis_content', '9')->first();
        $footer   = Konten::where('jenis_content', 7)->get();
        $tertiaryContent  = Konten::where('jenis_content', 5)->first();

        $lokasiKavling = LokasiKavling::withCount('kavlingPeta')->get();

        return view('frontend.homepage.kavling', compact(
            'logo',
            'footer',
            'tertiaryContent',
            'lokasiKavling',
            'progres'
        ));
    }


    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = Konten::findOrFail($id);

            if (! empty($data->nama_file) && file_exists(public_path('assets/konten/' . $data->nama_file))) {
                unlink(public_path('assets/konten/' . $data->nama_file));
            }

            $data->delete();

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'error'  => $e->getMessage(),
            ], 500);
        }
    }

}
