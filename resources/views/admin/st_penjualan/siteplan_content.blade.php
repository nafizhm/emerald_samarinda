<div class="siteplan-loaded">
<div class="siteplan-toolbar">
    <div class="siteplan-location"><strong>{{ $lokasi->nama_kavling }}</strong><span>{{ $lokasi->kavlingPeta->count() }} kavling</span></div>
    <div class="siteplan-tools">
        <button type="button" class="siteplan-tool" data-zoom="out" aria-label="Perkecil denah" title="Perkecil"><i class="bi bi-dash-lg"></i></button>
        <span class="siteplan-zoom-level" aria-live="polite">100%</span>
        <button type="button" class="siteplan-tool" data-zoom="in" aria-label="Perbesar denah" title="Perbesar"><i class="bi bi-plus-lg"></i></button>
        <button type="button" class="siteplan-tool reset-button" aria-label="Reset posisi dan zoom denah" title="Reset denah"><i class="bi bi-arrow-counterclockwise"></i></button>
        <span class="siteplan-tool-divider"></span>
        <a href="{{ route('denah-penjualan.cetak.pdf', $lokasi->id) }}" target="_blank" rel="noopener" class="siteplan-tool" title="Cetak PDF" aria-label="Cetak denah PDF"><i class="bi bi-file-earmark-pdf"></i></a>
        <a href="{{ route('denah-penjualan.cetak.jpg', $lokasi->id) }}" target="_blank" rel="noopener" class="siteplan-tool" title="Download JPG" aria-label="Download denah JPG"><i class="bi bi-download"></i></a>
    </div>
</div>
<div class="svg-container" aria-label="Denah {{ $lokasi->nama_kavling }}">

    {{-- SVG Header --}}
    @if ($lokasi->masterSvg)
        {!! str_replace(['[[lebar]]', '[[tinggi]]'], ['100%', '100%'], $lokasi->masterSvg->header_svg) !!}
    @endif

    {{-- Loop kavling --}}
    @foreach ($lokasi->kavlingPeta as $pt)
        @php
            $warna = $pt->customer ? '#dbe4ef' : '#ffffff';
            if ($pt->customer) {
                if ($pt->progres) {
                    $warna = $pt->progres->warna ?: '#dbe4ef';
                }
            } else {
                if ($pt->registrasi) {
                    if ($pt->registrasi->stt_reg == 0) {
                        $warna = '#00ffff';
                    } elseif (in_array($pt->registrasi->stt_reg, [1, 2, 3])) {
                        $warna = '#ffffff';
                    }
                }
            }
        @endphp

        @php $svg_code = $pt->jenis_map === 'polygon' ? $lokasi->masterSvg->polygon_svg : $lokasi->masterSvg->path_svg; @endphp

        <a href="javascript:void(0);" data-id="{{ $pt->id }}"
            data-available="{{ $pt->customer ? '0' : '1' }}" tabindex="0" role="button" aria-label="Rincian kavling {{ $pt->kode_kavling }}"
            data-url="{{ route('st-penjualan.detail', $pt->id) }}" data-bs-toggle="modal"
            data-bs-target="#modalDetail" class="detail-button">
            {!! str_replace(
                ['[[1]]', '[[2]]', '[[3]]', '[[4]]'],
                [$pt->map, $warna, $pt->matrik, $pt->kode_kavling],
                $svg_code,
            ) !!}
        </a>
    @endforeach

    {{-- SVG Footer --}}
    @if ($lokasi->masterSvg)
        {!! $lokasi->masterSvg->footer_svg !!}
    @endif
</div>
<div class="siteplan-map-hint"><i class="bi bi-hand-index"></i>Geser untuk memindahkan denah · Gunakan +/− atau roda mouse untuk zoom · Klik kavling untuk rincian</div>
</div>
