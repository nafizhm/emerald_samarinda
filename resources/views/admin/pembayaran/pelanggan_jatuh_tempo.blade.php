@extends('admin.layout')
@section('page_title', 'Pelanggan Jatuh Tempo')
@section('content')
<div class="dashboard-page due-page">
    <div class="dashboard-intro">
        <div><p class="dashboard-eyebrow">Pengingat pembayaran</p><h1>Pelanggan jatuh tempo</h1><p>Prioritaskan cicilan yang belum dibayar, lalu hubungi pelanggan melalui WhatsApp.</p></div>
        <div class="dashboard-date"><i class="bi bi-calendar3"></i>{{ now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}</div>
    </div>
    <div class="dashboard-grid metrics-grid">
        <article class="metric-card"><div class="metric-heading"><span>Perlu ditindaklanjuti</span><span class="metric-icon"><i class="bi bi-people"></i></span></div><div class="metric-value">{{ $summary['total'] }} <small>pelanggan</small></div><a class="detail-link stretched-link" href="{{ route('pelanggan-jatuh-tempo.index') }}">Lihat semua <i class="bi bi-arrow-right"></i></a></article>
        <article class="metric-card"><div class="metric-heading"><span>Jatuh tempo hari ini</span><span class="metric-icon amber"><i class="bi bi-calendar-event"></i></span></div><div class="metric-value">{{ $summary['today'] }} <small>pelanggan</small></div><a class="detail-link stretched-link" href="{{ route('pelanggan-jatuh-tempo.index', ['status' => 'hari-ini']) }}">Lihat hari ini <i class="bi bi-arrow-right"></i></a></article>
        <article class="metric-card"><div class="metric-heading"><span>Sudah terlambat</span><span class="metric-icon due-red"><i class="bi bi-clock-history"></i></span></div><div class="metric-value">{{ $summary['late'] }} <small>pelanggan</small></div><a class="detail-link stretched-link" href="{{ route('pelanggan-jatuh-tempo.index', ['status' => 'terlambat']) }}">Lihat tunggakan <i class="bi bi-arrow-right"></i></a></article>
        <article class="metric-card"><div class="metric-heading"><span>Nominal jatuh tempo</span><span class="metric-icon green"><i class="bi bi-wallet2"></i></span></div><div class="due-amount-summary"><small>Rp</small> {{ number_format($summary['amount'], 0, ',', '.') }}</div><p class="metric-note">Cicilan jatuh tempo yang belum dibayar</p></article>
    </div>
    <section class="dashboard-panel" aria-labelledby="due-list-title">
        <div class="panel-heading"><div><h2 id="due-list-title">Daftar pelanggan</h2><p>Urutan dimulai dari cicilan belum dibayar yang paling lama jatuh tempo.</p></div><span class="dashboard-date">{{ $customers->total() }} hasil</span></div>
        <form method="GET" action="{{ route('pelanggan-jatuh-tempo.index') }}" class="due-filters" role="search">
            <div class="due-search"><i class="bi bi-search"></i><input type="search" name="q" value="{{ $search }}" placeholder="Cari nama, kode kavling, atau lokasi..." aria-label="Cari pelanggan jatuh tempo"></div>
            <select name="status" class="dashboard-filter" aria-label="Filter status jatuh tempo"><option value="semua" @selected($status === 'semua')>Semua status</option><option value="hari-ini" @selected($status === 'hari-ini')>Jatuh tempo hari ini</option><option value="terlambat" @selected($status === 'terlambat')>Sudah terlambat</option></select>
            <button class="dashboard-action due-filter-button" type="submit"><i class="bi bi-funnel"></i>Tampilkan</button>
            @if($search || $status !== 'semua')<a class="detail-link" href="{{ route('pelanggan-jatuh-tempo.index') }}">Reset</a>@endif
        </form>
        <div class="table-responsive">
            <table class="dashboard-table due-table"><thead><tr><th>Pelanggan</th><th>Kavling / Lokasi</th><th>Jatuh tempo</th><th>Status</th><th class="text-end">Nominal jatuh tempo</th><th class="text-end">Tindak lanjut</th></tr></thead>
                <tbody>@forelse($customers as $row)<tr>
                    <td><span class="location-name">{{ $row['name'] }}</span><span class="location-code">{{ $row['phone'] ?: 'Nomor WhatsApp belum tersedia' }}</span></td>
                    <td><span class="fw-semibold">{{ $row['codes'] }}</span><span class="location-code">{{ $row['locations'] }}</span></td>
                    <td>{{ $row['due_date']->locale('id')->translatedFormat('d M Y') }}</td>
                    <td><span class="due-status {{ $row['late_days'] ? 'late' : 'today' }}">{{ $row['late_days'] ? 'Telat '.$row['late_days'].' hari' : 'Hari ini' }}</span></td>
                    <td class="text-end fw-semibold">Rp {{ number_format($row['due_amount'], 0, ',', '.') }}</td>
                    <td><div class="due-actions">@if($row['whatsapp_url'])<a class="due-contact" href="{{ $row['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Hubungi {{ $row['name'] }} melalui WhatsApp"><i class="bi bi-whatsapp"></i>WhatsApp</a>@else<span class="due-contact unavailable" title="Lengkapi nomor WhatsApp pelanggan terlebih dahulu"><i class="bi bi-whatsapp"></i>Tanpa nomor</span>@endif<a class="detail-link" href="{{ route('pembayaran.detail', $row['id']) }}" aria-label="Lihat pembayaran {{ $row['name'] }}">Detail <i class="bi bi-arrow-up-right"></i></a></div></td>
                </tr>@empty<tr><td colspan="6"><div class="due-empty"><span class="due-empty-icon"><i class="bi {{ $search || $status !== 'semua' ? 'bi-search' : 'bi-check2-circle' }}"></i></span><h3>{{ $search || $status !== 'semua' ? 'Tidak ada hasil yang cocok' : 'Tidak ada cicilan jatuh tempo' }}</h3><p>{{ $search || $status !== 'semua' ? 'Coba kata pencarian atau status yang berbeda.' : 'Belum ada pelanggan aktif dengan cicilan jatuh tempo yang belum dibayar.' }}</p></div></td></tr>@endforelse</tbody>
            </table>
        </div>
        @if($customers->hasPages())<div class="due-pagination"><span>{{ $customers->firstItem() }}–{{ $customers->lastItem() }} dari {{ $customers->total() }} pelanggan</span><div>@if($customers->onFirstPage())<span class="dashboard-action text-muted">Sebelumnya</span>@else<a class="dashboard-action" href="{{ $customers->previousPageUrl() }}">Sebelumnya</a>@endif<span class="mx-2">{{ $customers->currentPage() }} / {{ $customers->lastPage() }}</span>@if($customers->hasMorePages())<a class="dashboard-action" href="{{ $customers->nextPageUrl() }}">Berikutnya</a>@else<span class="dashboard-action text-muted">Berikutnya</span>@endif</div></div>@endif
    </section>
    <p class="due-note"><i class="bi bi-info-circle"></i>Daftar mengikuti jadwal cicilan dan pembayaran yang sudah tercatat. Tombol WhatsApp membuka draf pengingat untuk Anda periksa sebelum dikirim.</p>
</div>
@endsection
