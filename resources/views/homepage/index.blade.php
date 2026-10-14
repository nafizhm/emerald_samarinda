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
        .menu-clip {
        transition: clip-path 0.5s ease-in-out;
        }

        
    @layer utilities {
    .bg-swipe2 {
        position: relative;
        overflow: hidden;
        z-index: 0;
    }

    .bg-swipe2::after {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgb(255, 255, 255);
        transition: all 0.4s ease;
        z-index: -1;
    }

    .bg-swipe2:hover::after {
        left: 0;
    }
    }
 
 
    @layer utilities {
    .bg-swipe3 {
        position: relative;
        overflow: hidden;
        z-index: 0;
    }

    .bg-swipe3::after {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgb(29, 57, 143);
        transition: all 0.4s ease;
        z-index: -1;
    }

    .bg-swipe3:hover::after {
        left: 0;
    }
    }
    </style>
    
</head>
<body>

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

<div class="md:mx-20 mx-5 mt-10">
  <div class="rounded-xl overflow-hidden shadow-lg">
    <div class="relative w-full h-[350px] md:h-[300px] overflow-hidden">

     <div id="sliderWrapper"
        class="flex transition-transform duration-500 ease-in-out h-full"
        style="transform: translateX(0%);">

        @foreach ($sliders as $slider)
            <div class="w-full h-full flex-shrink-0">
                <img src="{{ asset('assets/konten/' . $slider->nama_file) }}"
                    class="w-full h-full object-cover"
                    alt="Slider Image {{ $loop->iteration }}">
            </div>
        @endforeach
    </div>
    </div>
  </div>

<div id="sliderIndicators" class="flex justify-center gap-3 mt-6"></div>
</div>

<section id="document" class="py-16 px-4 bg-gray-50">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Download Berkas</h2>
            <p class="text-gray-600">Klik pada card untuk mengunduh file</p>
        </div>

        @if($document->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($document as $doc)
                    <a href="{{ asset('assets/konten/' . $doc->nama_file) }}" 
                       download="{{ $doc->nama_file }}"
                       class="group block bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200 hover:border-blue-500 cursor-pointer transform hover:-translate-y-1">
                        
                        <div class="p-6">
                            <!-- Icon -->
                            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full group-hover:bg-blue-500 transition-colors duration-300">
                                <svg class="w-8 h-8 text-blue-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>

                            <!-- Title -->
                            <h3 class="text-lg font-semibold text-gray-900 text-center mb-2 group-hover:text-blue-600 transition-colors duration-300">
                                {{ $doc->judul }}
                            </h3>

                            <!-- File Info -->
                            <div class="flex items-center justify-center text-sm text-gray-500 mb-4">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <span>{{ pathinfo($doc->nama_file, PATHINFO_EXTENSION) }}</span>
                            </div>

                            <!-- Download Button -->
                            <div class="flex items-center justify-center text-blue-600 group-hover:text-blue-700 font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Download
                            </div>
                        </div>

                        <div class="h-2 bg-gradient-to-r from-blue-500 to-blue-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-500 text-lg">Tidak ada konten tersedia</p>
            </div>
        @endif
    </div>
</section>

<section  id="{{ $aboutus->url_item ?? 'aboutus' }}" class="bg-white py-16 px-6 md:px-16">
    <div class="max-w-7xl mx-auto">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <div class="w-full order-1 lg:order-1">
                <div class="rounded-2xl overflow-hidden shadow-xl">
                    @if (!empty($aboutUs?->nama_file))
                        <img 
                            src="{{ asset('assets/konten/' . $aboutUs->nama_file) }}"
                            alt="{{ $aboutUs->judul ?? 'About Us' }}"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <img 
                            src="https://via.placeholder.com/800x600?text=No+Image"
                            class="w-full h-full object-cover opacity-50"
                            alt="No image"
                        >
                    @endif
                </div>
            </div>

            <div class="w-full order-2 lg:order-2">
                <div class="space-y-6">

                    @if (!empty($aboutUs?->judul))
                        <h2 class="text-3xl font-bold text-gray-700 poppins">
                            {{ $aboutUs->judul }}
                        </h2>
                    @endif

                    @if (!empty($aboutUs?->artikel))
                        <div class="text-gray-700 leading-relaxed text-justify poppins prose max-w-none">
                            {!! $aboutUs->artikel !!}
                        </div>
                    @else
                        <p class="text-gray-500 italic">
                            Konten belum tersedia.
                        </p>
                    @endif

                    <div class="flex flex-col md:flex-row items-start gap-4 md:items-center justify-between">
                        <span class="flex flex-col gap-1">
                            <h1 class="font-medium text-gray-800 text-lg ">Hubungi Kami</h1>
                            <p class="md:text-sm text-xs text-gray-600 md:w-full ">Konsultasi Gratis Bersama Admin Marketing Kami</p>
                        </span>
                        <a class="bg-blue-800 hover:bg-blue-900 py-2 text-sm px-4 rounded-sm text-white font-medium" href="#">Klik disini</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<section id="{{ $fasilitas->url_item ?? 'fasilitas' }}" class="py-2 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-700">
                Our Facilities
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">

            @foreach ($facilities as $facility)
                <div 
                    class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 p-8 border border-gray-100"
                    data-aos="fade-up"
                    data-aos-duration="800"
                >
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6
                        bg-gradient-to-br from-blue-300 to-blue-500 text-white text-3xl shadow-md">
                        <i class="{{ $facility->icon }}"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 text-center mb-3">
                        {{ $facility->judul }}
                    </h3>

                    <p class="text-gray-600 text-center leading-relaxed mb-4">
                        {{ ($facility->artikel) }}
                    </p>

                </div>
            @endforeach

        </div>
    </div>
</section>


@php
$properties = $product; 
$totalSlides = ceil(count($properties) / 2);
@endphp

<section class="bg-gray-50 py-16 px-4">
    <div class="max-w-7xl mx-auto">

        <div class="flex items-center justify-between mx-5 mb-12">
            <h2 id="produk" class="md:text-4xl text-xl font-serif font-semibold text-gray-800">
                Our Product
            </h2>

            <a href="#"
               class="border border-gray-800 ml-20 text-gray-800 hover:text-white transition-all 
               font-semibold py-2 sm:px-5 px-3 text-center text-xs sm:text-sm cursor-pointer 
               rounded-md min-w-[120px] h-10 flex items-center justify-center poppins relative overflow-hidden
               before:content-[''] before:absolute before:top-0 before:left-[-100%]
               before:w-full before:h-full before:bg-blue-700 before:transition-all before:duration-300
               hover:before:left-0 hover:border-blue-700">
                See All Properties
            </a>
        </div>

        <div class="relative">
            <div class="overflow-hidden">
                <div id="cardSlider"
                     class="flex transition-transform duration-500 ease-in-out"
                     style="transform: translateX(0)">
                     
                    @for ($i = 0; $i < $totalSlides; $i++)
                        <div class="w-full flex-shrink-0">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                @foreach ($properties->slice($i * 2, 2) as $property)
                                    <div class="flex-shrink-0 w-full px-3">
                                        <div class="bg-white rounded-lg overflow-hidden shadow-lg">

                                            <div class="relative h-80 overflow-hidden group">
                                                <img src="{{ asset('assets/konten/' . $property->nama_file) }}"
                                                     class="w-full h-full object-cover flex-shrink-0">
                                            </div>

                                            <div class="p-6">
                                                <h3 class="text-2xl playfair text-gray-800 font-medium mb-6">
                                                    {{ $property->judul }}
                                                </h3>

                                                <p class="text-gray-700 text-sm leading-relaxed">
                                                    {!! $property->artikel !!}
                                                </p>

                                                <div class="flex items-center justify-end mt-6">
                                                    <a href="#"
                                                       class="bg-blue-950 text-white px-4 py-2 rounded font-semibold 
                                                              hover:bg-blue-900 transition-colors">
                                                        See Details
                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    @endfor

                </div>
            </div>

            <div class="flex justify-center gap-3 mt-8">
                @for ($i = 0; $i < $totalSlides; $i++)
                    <button class="dot w-3 h-3 rounded-full bg-gray-400 hover:bg-gray-500 transition-all"
                        data-slide="{{ $i }}"></button>
                @endfor
            </div>
        </div>

    </div>
</section>


 <div id="{{ $siteplan->url_item ?? 'siteplan' }}" class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="text-start mx-5 mb-10">
        <h1 class="text-4xl font-bold text-gray-800 mb-3">Site Plan Kavling</h1>
        <p class="text-gray-600 text-lg">Pilih lokasi kavling untuk melihat detail unit</p>
    </div>

    @if($lokasiKavling->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($lokasiKavling as $lokasi)
        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer overflow-hidden"
             onclick="window.location.href='/siteplan/{{ $lokasi->id }}'">
            
            <div class="relative h-56 overflow-hidden bg-gray-200">
                @if($lokasi->foto_kavling)
                <img src="{{ asset('assets/homepage/' . $lokasi->foto_kavling) }}" 
                     alt="{{ $lokasi->nama_kavling }}"
                     class="w-full h-full object-cover transition-transform duration-300 hover:scale-110">
                @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-600 to-blue-800">
                    <svg class="w-16 h-16 text-white opacity-50" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </div>
                @endif
                
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                    <h2 class="text-xl font-semibold text-white mb-1">{{ $lokasi->nama_kavling }}</h2>
                    @if($lokasi->nama_singkat)
                    <p class="text-white/90 text-sm">{{ $lokasi->nama_singkat }}</p>
                    @endif
                </div>
            </div>
            
            <div class="p-6">
                <div class="flex items-start mb-5">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 mr-3">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 font-medium mb-1">Alamat</p>
                        <p class="text-gray-800 text-sm leading-relaxed">{{ $lokasi->alamat ?: 'Alamat belum tersedia' }}</p>
                    </div>
                </div>

                <a href="{{ route('kavling.detail', $lokasi->id) }}" class="block">
                    <button class="w-full cursor-pointer bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                        Lihat Detail
                    </button>
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-16">
        <svg class="w-20 h-20 mx-auto mb-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
        </svg>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Data Kavling</h3>
        <p class="text-gray-500">Silakan tambahkan data lokasi kavling terlebih dahulu</p>
    </div>
    @endif
</div>




<footer class="bg-[#003B73] text-white pt-12 pb-10 text-sm">
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
document.addEventListener("DOMContentLoaded", () => {
    const navHeight = document.getElementById("navbar").offsetHeight;

    function smoothScroll(e) {
        const href = this.getAttribute("href");
        if (!href || !href.startsWith("#")) return;

        const target = document.querySelector(href);
        if (!target) return;

        e.preventDefault();

        window.scrollTo({
            top: target.offsetTop - navHeight - 5,
            behavior: "smooth"
        });

        if (window.innerWidth < 768 && typeof toggleMenu === "function") {
            toggleMenu();
        }
    }

    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener("click", smoothScroll);
    });
});
</script>


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


//   bagian slider
  const slides = document.querySelectorAll("#sliderWrapper > div").length;
  const sliderWrapper = document.getElementById("sliderWrapper");
  const indicatorContainer = document.getElementById("sliderIndicators");

  let currentSlide = 0;
  const intervalTime = 3500;

  for (let i = 0; i < slides; i++) {
    const dot = document.createElement("button");
    dot.setAttribute("data-slide", i);
    dot.className = "h-3 w-3 bg-gray-400 rounded-full transition-all hover:bg-gray-500";
    indicatorContainer.appendChild(dot);
  }

  const updateIndicator = () => {
    const dots = indicatorContainer.querySelectorAll("button");
    dots.forEach((dot, i) => {
      if (i === currentSlide) {
        dot.classList.remove("w-3", "bg-gray-400");
        dot.classList.add("w-8", "bg-blue-950");
      } else {
        dot.classList.remove("w-8", "bg-blue-950");
        dot.classList.add("w-3", "bg-gray-400");
      }
    });
  };

  updateIndicator();

  const nextSlide = () => {
    currentSlide = (currentSlide + 1) % slides;
    sliderWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
    updateIndicator();
  };

  let slideInterval = setInterval(nextSlide, intervalTime);

  indicatorContainer.addEventListener("click", (e) => {
    if (e.target.tagName === "BUTTON") {
      clearInterval(slideInterval);
      currentSlide = parseInt(e.target.getAttribute("data-slide"));
      sliderWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
      updateIndicator();
      slideInterval = setInterval(nextSlide, intervalTime);
    }
  });

//   slider card
document.addEventListener("DOMContentLoaded", () => {

    const slider = document.getElementById("cardSlider");
    const dots = document.querySelectorAll(".dot");
    const totalSlides = dots.length;
    let currentSlide = 0;

    function updateCardSlider() {
        slider.style.transform = `translateX(-${currentSlide * 100}%)`;
        dots.forEach((d, i) => {
            d.classList.remove("bg-blue-950", "w-8");
            d.classList.add("bg-gray-400");

            if (i === currentSlide) {
                d.classList.add("bg-blue-950", "w-8");
            }
        });
    }

    dots.forEach(btn => {
        btn.addEventListener("click", () => {
            currentSlide = parseInt(btn.dataset.slide);
            updateCardSlider();
        });
    });

    setInterval(() => {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateCardSlider();
    }, 5000);



    const cards = document.querySelectorAll("[data-images]");

    cards.forEach(card => {
        const images = JSON.parse(card.dataset.images);
        const track = card.querySelector(".image-track");
        const prev = card.querySelector(".prev");
        const next = card.querySelector(".next");

        let index = 0;

        function updateImg() {
            track.style.transform = `translateX(-${index * 100}%)`;
        }

        next.addEventListener("click", () => {
            index = (index + 1) % images.length;
            updateImg();
        });

        prev.addEventListener("click", () => {
            index = (index - 1 + images.length) % images.length;
            updateImg();
        });

        setInterval(() => {
            index = (index + 1) % images.length;
            updateImg();
        }, 3000);

    });

});
</script>

</html>