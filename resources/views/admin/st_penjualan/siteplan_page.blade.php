<div class="dashboard-page siteplan-page">
    <div class="dashboard-intro">
        <div><p class="dashboard-eyebrow">Ketersediaan & penjualan</p><h1>Denah Penjualan</h1><p>Pilih lokasi, lihat status kavling, lalu klik unit untuk membuka rinciannya.</p></div>
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
            <div class="panel-heading"><div><h2>Keterangan warna</h2><p>Dasar pewarnaan kavling</p></div><i class="bi bi-palette text-muted"></i></div>
            <div class="siteplan-color-mode-wrap">
                <select class="form-select" id="siteplan-color-mode" aria-label="Pilih dasar warna denah">
                    <option value="skema">Skema Pembayaran</option>
                    <option value="tahap">Tahap Pembayaran</option>
                </select>
            </div>
            <div class="siteplan-legend-items">
                @foreach($legend['skema'] as $nama => $warna)<div class="siteplan-legend-item" data-legend="skema"><span class="siteplan-swatch" style="background:{{ $warna }}"></span><span>{{ $nama }}</span></div>@endforeach
                @foreach($legend['tahap'] as $nama => $warna)<div class="siteplan-legend-item" data-legend="tahap" hidden><span class="siteplan-swatch" style="background:{{ $warna }}"></span><span>{{ $nama }}</span></div>@endforeach
            </div>
            <div class="siteplan-legend-note"><i class="bi bi-info-circle"></i><p>Kavling yang belum memiliki customer ditampilkan putih.</p></div>
        </aside>
    </div>
    @else
    <section class="dashboard-panel"><div class="siteplan-state"><i class="bi bi-map"></i><h2>Belum ada lokasi yang ditampilkan</h2><p>Aktifkan lokasi kavling agar denah tersedia di halaman ini.</p></div></section>
    @endif
</div>
