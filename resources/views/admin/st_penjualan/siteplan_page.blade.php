<div class="dashboard-page siteplan-page">
    <div class="dashboard-intro">
        <div><p class="dashboard-eyebrow">Ketersediaan & penjualan</p><h1>Denah penjualan</h1><p>Pilih lokasi, lihat status kavling, lalu klik unit untuk membuka rinciannya.</p></div>
        <button type="button" class="dashboard-action" id="legend-toggle" aria-controls="legend" aria-expanded="true" onclick="toggleLegend()"><i class="bi bi-palette"></i><span>Keterangan warna</span></button>
    </div>
    @if($lokasiKavling->isNotEmpty())
    <ul class="nav siteplan-tabs" id="lokasiTab" role="tablist">
        @foreach($lokasiKavling as $index => $kav)
        <li class="nav-item" role="presentation"><a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="lokasi-{{ $kav->id }}-tab" data-bs-toggle="tab" href="#lokasi-{{ $kav->id }}" role="tab" aria-controls="lokasi-{{ $kav->id }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}" onclick="loadSiteplanData({{ $kav->id }})"><i class="bi bi-geo-alt"></i>{{ $kav->nama_kavling }}</a></li>
        @endforeach
    </ul>
    <div class="siteplan-workspace" id="siteplan-workspace">
        <section class="dashboard-panel siteplan-map-panel" aria-label="Area denah kavling">
            <div class="tab-content" id="lokasiTabContent">
                @foreach($lokasiKavling as $index => $kav)
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="lokasi-{{ $kav->id }}" role="tabpanel" aria-labelledby="lokasi-{{ $kav->id }}-tab" data-id-lokasi="{{ $kav->id }}"><div class="siteplan-content"><div class="siteplan-state"><i class="bi bi-map"></i><p>Pilih lokasi untuk menampilkan denah.</p></div></div></div>
                @endforeach
            </div>
        </section>
        <aside class="dashboard-panel siteplan-legend" id="legend" aria-label="Keterangan warna kavling">
            <div class="panel-heading"><div><h2>Keterangan warna</h2><p>Status setiap kavling</p></div><i class="bi bi-palette text-muted"></i></div>
            <div class="siteplan-legend-items">
                <div class="siteplan-legend-item"><span class="siteplan-swatch available"></span><span>Belum dialokasikan</span></div>
                @foreach($legend as $item)<div class="siteplan-legend-item"><span class="siteplan-swatch" style="background:{{ $item->warna }}"></span><span>{{ $item->status_progres }}</span></div>@endforeach
                <div class="siteplan-legend-item"><span class="siteplan-swatch unclassified"></span><span>Belum ada status</span></div>
            </div>
            <div class="siteplan-legend-note"><i class="bi bi-info-circle"></i><p>Warna mengikuti tahapan pelanggan. Kavling berwarna netral belum memiliki status penjualan.</p></div>
        </aside>
    </div>
    @else
    <section class="dashboard-panel"><div class="siteplan-state"><i class="bi bi-map"></i><h2>Belum ada lokasi yang ditampilkan</h2><p>Aktifkan lokasi kavling agar denah tersedia di halaman ini.</p></div></section>
    @endif
</div>
