<!DOCTYPE html>
<html lang="en">
     @php
        use Illuminate\Support\Facades\DB;

        $namaPerusahaan = DB::table('konfigurasi')->value('nama_perusahaan');

        $favicon = DB::table('konfigurasi_media')
            ->where('jenis_data', 'fav icon')
            ->value('nama_file');

        $logo = DB::table('konfigurasi_media')
            ->where('jenis_data', 'logo website')
            ->value('nama_file');
    @endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
            referrerpolicy="no-referrer" />
    <title>{{ $namaPerusahaan ?? 'Aplikasi Admin' }}</title>
    <link rel="shortcut icon" href="{{ asset('config_media/' . ($favicon ?? 'default-fav.png')) }}" type="image/x-icon">

    <style>
        .svg-content svg {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }
    </style>
</head>
<body class="bg-gray-50">
    

<div id="menu" class="menu">
  <nav id="navbar"
    class="fixed w-full py-3 z-50 px-6 md:px-20 text-white transition-all duration-300 bg-transparent"
  >
    <div class="container mx-auto flex items-center justify-between">
      
      <div class="flex items-center">
        <img src="{{ asset('config_media/' . ($logo ?? 'default-fav.png')) }}" class="w-[60px]" />
      </div>

      <div class="hidden md:flex items-center">
        <ul class="flex  gap-6 text-lg text-white text-center">
            <a href="{{ url('/') }}" >Kembali</a>
        </ul>
      </div>
    </div>

    <button id="navTgl"
      class="nav-tgl fixed right-4 top-4 w-8 h-8 rounded-full bg-white z-[100] shadow-md flex items-center justify-center md:hidden"
    >
      <span class="relative w-5 h-[14px] flex flex-col justify-between items-center">
        <span id="line1" class="h-[1.5px] bg-[#293335] rounded transition-all duration-200 w-4"></span>
        <span id="line2" class="h-[1.5px] bg-[#293335] rounded transition-all duration-200 w-3"></span>
        <span id="line3" class="h-[1.5px] bg-[#293335] rounded transition-all duration-200 w-4"></span>
      </span>
    </button>

    <div id="overlay"
      class="fixed top-0 left-0 z-50 w-screen h-screen transition-all duration-500 ease-in-out invisible bg-black/50 backdrop-blur-md backdrop-saturate-150 menu-clip"
      style="clip-path: circle(20px at calc(100% - 30px) 50px)"
    ></div>

    <div id="mobileMenu"
      class="fixed top-0 left-0 w-screen h-screen z-[99] transition-opacity duration-500 opacity-0 pointer-events-none flex items-center justify-center"
    >
     <ul class="flex flex-col gap-6 text-lg text-white text-center">
        @foreach ($navItems as $item)
            <li>
                <a href="{{ $item->url_item ? '#' . $item->url_item : '#' }}"
                    class="{{ request()->url() == url($item->artikel) ? 'custom-navbar-active' : '' }}">
                    {{ $item->judul }}
                </a>
            </li>
        @endforeach

    </ul>

    </div>

  </nav>
</div>


<section id="{{ $beranda->url_item ?? 'beranda' }}"
    class="w-full h-[60vh] bg-cover bg-center flex items-center relative"
    style="background-image: url('{{ $heroSection && $heroSection->nama_file 
        ? asset('assets/konten/' . $heroSection->nama_file) 
        : asset('assets/homepage/bg-main.jpg') }}');"
    >

    <div class="flex flex-col z-10  px-4 items-start md:text-left md:px-0 md:ml-24">
        <h1 class="text-white font-bold text-xl md:text-4xl">
            {{ $heroSection->judul }}
        </h1>

        <p class="text-white text-xs md:text-sm mt-2">
            {{ $heroSection->artikel }}
        </p>
    </div>


  <div class="absolute inset-0 bg-[linear-gradient(to_top,rgba(30,58,138),rgba(30,58,138,0.7),transparent)]"></div>
</section>

<div class="container mx-auto px-4 py-8 mt-5">
    <div class="max-w-4xl mx-auto">
        
        <div class="mb-6 text-start">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">{{ $kav->nama_kavling }}</h1>
            <p class="text-gray-600">{{ $kav->alamat }}</p>
        </div>

        <hr class="mb-6">

        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
            
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 border-b border-gray-200">
                <div class="flex flex-wrap items-center justify-center gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-white border border-gray-500"></div>
                        <span class="text-gray-700">Tersedia</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-green-600 border border-green-700"></div>
                        <span class="text-gray-700">Terjual</span>
                    </div>
                </div>
            </div>

            <div class="p-4 md:p-6 bg-gray-50">
                <div class="bg-white rounded-lg shadow-inner overflow-hidden">
                    
                    <div class="svg-container-wrapper w-full flex justify-center items-center p-4" style="height: 450px;">
                        <div class="svg-content w-full h-full flex justify-center items-center">
                            
                            @if ($kav->masterSvg)
                                {!! str_replace(['[[lebar]]', '[[tinggi]]'], ['100%', '100%'], $kav->masterSvg->header_svg) !!}
                            @endif

                            @foreach ($kav->kavlingPeta as $pt)
                                @php
                                    $warna = '#ffffff';
                                    $customer = $pt->customer->first();

                                    if ($customer && $customer->progres) {
                                        $warna = $customer->progres->warna;
                                    } elseif ($pt->status == 1) {
                                        $warna = '#42f202';
                                    }
                                @endphp

                                @if ($pt->jenis_map === 'polygon')
                                    {!! str_replace(
                                        ['[[1]]', '[[2]]', '[[3]]', '[[4]]'],
                                        [$pt->map, $warna, $pt->matrik, $pt->kode_kavling],
                                        $kav->masterSvg->polygon_svg
                                    ) !!}
                                @else
                                    {!! str_replace(
                                        ['[[1]]', '[[2]]', '[[3]]', '[[4]]'],
                                        [$pt->map, $warna, $pt->matrik, $pt->kode_kavling],
                                        $kav->masterSvg->path_svg
                                    ) !!}
                                @endif
                            @endforeach

                            @if ($kav->masterSvg)
                                {!! $kav->masterSvg->footer_svg !!}
                            @endif
                            
                        </div>
                    </div>

                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span>Klik pada kavling untuk melihat detail</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-sync-alt text-blue-500"></i>
                        <span>Update terakhir: {{ date('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Informasi Statistik -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Kavling Tersedia</p>
                        <p class="text-xl font-bold text-gray-800">{{ $kav->kavlingPeta->where('status', 1)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-map-marked-alt text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Kavling</p>
                        <p class="text-xl font-bold text-gray-800">{{ $kav->kavlingPeta->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-home text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Kavling Terjual</p>
                        <p class="text-xl font-bold text-gray-800">{{ $kav->kavlingPeta->filter(function($item) { return $item->customer->isNotEmpty(); })->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<footer class="bg-[#003B73] text-white pt-12 pb-10 text-sm mt-20">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12">

        <div class="space-y-6 md:col-span-2">
            <img 
                src="{{ asset('config_media/' . ($logo ?? 'default-fav.png')) }}" 
                class="w-10"
                alt="logo"
            />

            @if($footer->first())
                <h1 class="text-xl font-semibold text-white">
                    {{ $footer->first()->judul }}
                </h1>

                <p class="leading-relaxed text-gray-200">
                    {!! $footer->first()->artikel !!}
                </p>
            @endif

           
        </div>

        <div class="space-y-6">
            <h3 class="font-semibold text-lg">Kantor</h3>

            <div class="space-y-4 text-gray-200">
                <p>{{ $kantor->alamat ?? '-' }}</p>
                <p>{{ $kantor->telp ?? '-' }}</p>
                <p>{{ $kantor->hape ?? '-' }}</p>
                <p>{{ $kantor->email ?? '-' }}</p>
            </div>

            
        </div>

        <div class="space-y-8">
            <div>
                <h3 class="font-semibold text-lg">Quick Menu</h3>
                <ul class="space-y-2 mt-3 text-gray-200">
                   @foreach ($navItems as $item)
                        <li>
                            <a href="{{ $item->url_item ? '#' . $item->url_item : '#' }}"
                                class="{{ request()->url() == url($item->artikel) ? 'custom-navbar-active' : '' }}">
                                {{ $item->judul }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>

    <hr class="mt-10" />

    <div class="text-center text-gray-300 text-xs mt-10">
        &copy; {{ date('Y') }} {{ $namaPerusahaan ?? 'Aplikasi Admin' }}. All Rights Reserved.
    </div>
</footer>

</body>

<script>
const nav = document.getElementById("navbar");
const navTgl = document.getElementById("navTgl");
const overlay = document.getElementById("overlay");
const mobileMenu = document.getElementById("mobileMenu");

const line1 = document.getElementById("line1");
const line2 = document.getElementById("line2");
const line3 = document.getElementById("line3");

let open = false;

window.addEventListener("scroll", () => {
  if (window.scrollY > 20) {
    nav.classList.add("bg-[#1c398e]", "shadow-lg");
    nav.classList.remove("bg-transparent", "backdrop-blur-md", "bg-opacity-80");
  } else {
    nav.classList.remove("bg-[#1c398e]", "shadow-lg");
    nav.classList.add("bg-transparent");
  }
});


function toggleMenu() {
  open = !open;

  if (open) {
    line1.classList.add("rotate-45", "translate-y-[6px]", "w-5");
    line2.classList.add("opacity-0");
    line3.classList.add("-rotate-45", "-translate-y-[6px]", "w-5");
    document.body.classList.add("overflow-hidden");

    overlay.style.visibility = "visible";
    overlay.style.clipPath = "circle(150% at calc(100% - 30px) 50px)";
    mobileMenu.style.opacity = "1";
    mobileMenu.style.pointerEvents = "auto";

  } else {
    line1.classList.remove("rotate-45", "translate-y-[6px]", "w-5");
    line2.classList.remove("opacity-0");
    line3.classList.remove("-rotate-45", "-translate-y-[6px]", "w-5");
    document.body.classList.remove("overflow-hidden");

    overlay.style.clipPath = "circle(20px at calc(100% - 30px) 50px)";
    setTimeout(() => {
      overlay.style.visibility = "hidden";
    }, 500);

    mobileMenu.style.opacity = "0";
    mobileMenu.style.pointerEvents = "none";
  }
}

navTgl.addEventListener("click", toggleMenu);
overlay.addEventListener("click", toggleMenu);
</script>

</html>