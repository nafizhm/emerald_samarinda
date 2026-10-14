@extends('admin.layout')
@section('page_title', 'Dashboard')
@section('content')
@php
    $allocationPercent = $jumlahKavling ? round($sudahLaku / $jumlahKavling * 100, 1) : 0;
@endphp
<div class="dashboard-page">
    <div class="dashboard-intro">
        <div><p class="dashboard-eyebrow">Ringkasan usaha</p><h1>Dashboard</h1><p>Pantau kavling, aktivitas penjualan, dan progres pelanggan dalam satu tempat.</p></div>
        <div class="dashboard-date"><i class="bi bi-calendar3"></i>{{ $dateNow }}</div>
    </div>
    <nav class="dashboard-quicklinks mb-4" aria-label="Aksi dashboard">
        <a class="dashboard-action" href="{{ route('dashboard.statistik-cicilan-customer') }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-receipt"></i>Rincian cicilan pelanggan<i class="bi bi-arrow-right"></i></a>
        <a class="dashboard-action" href="{{ route('customer.create') }}"><i class="bi bi-person-plus"></i>Transaksi baru / tambah pelanggan<i class="bi bi-arrow-right"></i></a>
    </nav>
    <div class="dashboard-grid metrics-grid">
        <article class="metric-card"><div class="metric-heading"><span>Total kavling</span><span class="metric-icon"><i class="bi bi-bounding-box"></i></span></div><div class="metric-value">{{ number_format($jumlahKavling, 0, ',', '.') }} <small>unit</small></div><p class="metric-note"><i class="bi bi-geo-alt"></i>{{ $dataLokasi->count() }} lokasi tampil</p></article>
        <article class="metric-card"><div class="metric-heading"><span>Teralokasi</span><span class="metric-icon green"><i class="bi bi-check2-circle"></i></span></div><div class="metric-value">{{ number_format($sudahLaku, 0, ',', '.') }} <small>unit</small></div><p class="metric-note">Terhubung dengan pelanggan aktif</p></article>
        <article class="metric-card"><div class="metric-heading"><span>Belum dialokasikan</span><span class="metric-icon amber"><i class="bi bi-grid"></i></span></div><div class="metric-value">{{ number_format($sisaLaku, 0, ',', '.') }} <small>unit</small></div><p class="metric-note">Belum terhubung pelanggan aktif</p></article>
        <article class="metric-card"><div class="metric-heading"><span>Persentase alokasi</span><span class="metric-icon"><i class="bi bi-pie-chart"></i></span></div><div class="metric-value">{{ number_format($allocationPercent, 1, ',', '.') }}<small>%</small></div><div class="metric-progress" role="progressbar" aria-label="Persentase alokasi kavling" aria-valuenow="{{ $allocationPercent }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ $allocationPercent }}%"></span></div><p class="metric-note">Dari keseluruhan kavling terdaftar</p></article>
    </div>
    <div class="dashboard-grid dashboard-two-col">
        <section class="dashboard-panel" aria-labelledby="sales-chart-title">
            <div class="panel-heading"><div><h2 id="sales-chart-title">Aktivitas transaksi</h2><p>Transaksi penjualan yang tercatat per bulan.</p></div><select class="dashboard-filter" id="tahun" aria-label="Tahun aktivitas transaksi"><option value="0">Semua tahun</option>@foreach($years as $year)<option value="{{ $year }}" @selected($year == now()->year)>{{ $year }}</option>@endforeach</select></div>
            <div class="panel-body"><div class="chart-area"><canvas id="barChart" role="img" aria-label="Grafik transaksi penjualan bulanan"></canvas><div class="chart-status" id="barChart-status" aria-live="polite">Memuat grafik...</div></div><p class="chart-footnote">Jumlah transaksi tercatat, bukan nilai rupiah atau jumlah kavling.</p></div>
        </section>
        <section class="dashboard-panel" aria-labelledby="marketing-chart-title">
            <div class="panel-heading"><div><h2 id="marketing-chart-title">Aktivitas tim marketing</h2><p>Bandingkan aktivitas transaksi berdasarkan marketing.</p></div></div>
            <div class="marketing-filters"><select class="dashboard-filter" id="tahun_2" aria-label="Tahun aktivitas marketing"><option value="0">Semua tahun</option>@foreach($years as $year)<option value="{{ $year }}" @selected($year == now()->year)>{{ $year }}</option>@endforeach</select><select class="dashboard-filter" id="marketing" aria-label="Pilih marketing"><option value="0">Semua marketing</option>@foreach($marketingList as $marketing)<option value="{{ $marketing->id }}">{{ $marketing->nama_marketing }}</option>@endforeach</select></div>
            <div class="panel-body"><div class="chart-area" style="height:207px"><canvas id="lineChart" role="img" aria-label="Grafik aktivitas transaksi marketing"></canvas><div class="chart-status" id="lineChart-status" aria-live="polite">Memuat grafik...</div></div><p class="chart-footnote">Ubah tahun atau marketing untuk melihat rincian.</p></div>
        </section>
    </div>
    <section class="dashboard-panel mb-4" aria-labelledby="location-summary-title">
        <div class="panel-heading"><div><h2 id="location-summary-title">Penjualan per lokasi</h2><p>Ringkasan unit dan tahapan pelanggan di setiap lokasi kavling.</p></div><span class="dashboard-date">{{ $dataLokasi->count() }} lokasi</span></div>
        <div class="table-responsive">
            <table class="dashboard-table"><thead><tr><th>Lokasi kavling</th><th class="text-center">Total unit</th><th class="text-center">Hold</th>@foreach($kolomStatus as $status)<th class="text-center" title="{{ $status->status_progres }}">{{ $status->short_name ?: $status->status_progres }}</th>@endforeach<th class="text-center">Bayar tunai</th><th class="text-center">Bayar kredit</th><th class="text-end">Rincian</th></tr></thead>
            <tbody>@forelse($dataLokasi as $lokasi)<tr><td><span class="location-name">{{ $lokasi['nama'] }}</span><span class="location-code">{{ $lokasi['nama_singk_1'] ?: '—' }}</span></td><td class="text-center fw-semibold">{{ $lokasi['jumlah'] }}</td><td class="text-center">{{ $lokasi['hold'] }}</td>@foreach($kolomStatus as $status)@php $key = strtolower(str_replace(' ', '_', $status->short_name)); @endphp<td class="text-center">{{ $lokasi[$key] ?? 0 }}</td>@endforeach<td class="text-center">{{ $lokasi['cash'] }}</td><td class="text-center">{{ $lokasi['kredit'] }}</td><td class="text-end"><a href="{{ route('dashboard.lokasi-penjualan-show', $lokasi['id']) }}" class="detail-link" aria-label="Lihat rincian {{ $lokasi['nama'] }}">Lihat <i class="bi bi-arrow-up-right"></i></a></td></tr>@empty<tr><td colspan="{{ 6 + $kolomStatus->count() }}"><div class="dashboard-empty"><i class="bi bi-map d-block fs-3 mb-2"></i>Belum ada lokasi yang ditampilkan.</div></td></tr>@endforelse</tbody>
            @if($dataLokasi->isNotEmpty())<tfoot><tr><td>Total</td><td class="text-center">{{ $totalLokasi['jumlah'] }}</td><td class="text-center">{{ $totalLokasi['hold'] }}</td>@foreach($kolomStatus as $status)@php $key = strtolower(str_replace(' ', '_', $status->short_name)); @endphp<td class="text-center">{{ $totalLokasi[$key] ?? 0 }}</td>@endforeach<td class="text-center">{{ $totalLokasi['cash'] }}</td><td class="text-center">{{ $totalLokasi['kredit'] }}</td><td></td></tr></tfoot>@endif
            </table>
        </div>
    </section>
    <div class="dashboard-grid dashboard-bottom-grid">
        <section class="dashboard-panel" aria-labelledby="progress-summary-title"><div class="panel-heading"><div><h2 id="progress-summary-title">Tahapan pelanggan</h2><p>Distribusi pelanggan aktif berdasarkan progres penjualan.</p></div><i class="bi bi-diagram-3 text-muted"></i></div>
            <div class="table-responsive"><table class="dashboard-table"><thead><tr><th>Tahapan</th><th class="text-end">Pelanggan</th><th>Porsi</th><th></th></tr></thead><tbody>@forelse($dataProgres as $item)<tr><td><span class="status-dot"></span>{{ $item['status_progres'] }}</td><td class="text-end fw-semibold">{{ $item['jumlah'] }}</td><td><div class="share-cell"><div class="share-track"><span style="width:{{ $item['persentase'] }}%"></span></div><span class="small">{{ $item['persentase'] }}%</span></div></td><td class="text-end"><a class="detail-link" href="{{ route('dashboard.customer-status-progres-show', $item['id_status_progres']) }}" aria-label="Lihat pelanggan pada tahapan {{ $item['status_progres'] }}"><i class="bi bi-arrow-up-right"></i></a></td></tr>@empty<tr><td colspan="4" class="dashboard-empty">Belum ada tahapan penjualan.</td></tr>@endforelse</tbody></table></div>
        </section>
        <section class="dashboard-panel" aria-labelledby="team-summary-title"><div class="panel-heading"><div><h2 id="team-summary-title">Kontribusi marketing</h2><p>Jumlah pelanggan aktif yang ditangani setiap marketing.</p></div><i class="bi bi-people text-muted"></i></div>
            <div class="table-responsive"><table class="dashboard-table"><thead><tr><th>Marketing</th><th class="text-end">Pelanggan</th><th>Porsi</th><th></th></tr></thead><tbody>@forelse($dataMarketing as $marketing)<tr><td class="fw-semibold">{{ $marketing['marketing'] }}</td><td class="text-end">{{ $marketing['jumlah'] }}</td><td><div class="share-cell"><div class="share-track"><span style="width:{{ $marketing['persentase'] }}%"></span></div><span class="small">{{ $marketing['persentase'] }}%</span></div></td><td class="text-end"><a class="detail-link" href="{{ route('dashboard.customer-marketing-show', $marketing['id_marketing']) }}" aria-label="Lihat pelanggan marketing {{ $marketing['marketing'] }}"><i class="bi bi-arrow-up-right"></i></a></td></tr>@empty<tr><td colspan="4" class="dashboard-empty">Belum ada data tim marketing.</td></tr>@endforelse</tbody></table></div>
        </section>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(function() {
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const charts = {}, requests = {};
    const definitions = {
        barChart: {url:@json(route('dashboard.chart1')),type:'bar',params:()=>({tahun:$('#tahun').val()}),label:'Transaksi penjualan',color:'#6683d4'},
        lineChart: {url:@json(route('dashboard.chart2')),type:'line',params:()=>({tahun:$('#tahun_2').val(),marketing:$('#marketing').val()}),label:'Transaksi marketing',color:'#55a58e'}
    };
    function options() {
        const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const text = dark ? '#95a6bf' : '#75839a', grid = dark ? '#2d394e' : '#edf0f5';
        return {responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{backgroundColor:dark?'#34415a':'#24334b',padding:11,displayColors:false}},scales:{x:{grid:{display:false},border:{display:false},ticks:{color:text,font:{size:11},maxRotation:0}},y:{beginAtZero:true,border:{display:false},grid:{color:grid},ticks:{color:text,precision:0,font:{size:11}}}}};
    }
    function loadChart(id) {
        if(requests[id]) requests[id].abort();
        const status = document.getElementById(id+'-status');
        status.hidden=false; status.textContent='Memuat grafik...';
        if(typeof Chart==='undefined') { status.textContent='Grafik tidak dapat dimuat. Silakan muat ulang halaman.'; return; }
        const definition=definitions[id];
        requests[id]=$.get(definition.url,definition.params()).done(function(data) {
            if(charts[id]) charts[id].destroy();
            charts[id]=new Chart(document.getElementById(id),{type:definition.type,data:{labels:months,datasets:[{label:definition.label,data,backgroundColor:definition.type==='line'?'rgba(85,165,142,.10)':definition.color,borderColor:definition.color,borderWidth:definition.type==='line'?2:0,borderRadius:definition.type==='bar'?5:0,maxBarThickness:24,fill:definition.type==='line',tension:.35,pointRadius:3,pointHoverRadius:5}]},options:options()});
            status.hidden=true;
            if(!data.some(value=>Number(value)>0)) {
                status.textContent='Belum ada transaksi pada pilihan ini.';status.hidden=false;
            }
        }).fail(function(xhr,state) {
            if(state==='abort') return;
            status.replaceChildren(document.createTextNode('Grafik gagal dimuat. '));
            const retry=document.createElement('button');retry.type='button';retry.textContent='Coba lagi';retry.onclick=()=>loadChart(id);status.appendChild(retry);
        }).always(function(){requests[id]=null;});
    }
    $('#tahun').on('change',()=>loadChart('barChart'));
    $('#tahun_2,#marketing').on('change',()=>loadChart('lineChart'));
    new MutationObserver(function(){Object.values(charts).forEach(function(chart){chart.options=options();chart.update('none');});}).observe(document.documentElement,{attributes:true,attributeFilter:['data-bs-theme']});
    loadChart('barChart');loadChart('lineChart');
});
</script>
@endpush
