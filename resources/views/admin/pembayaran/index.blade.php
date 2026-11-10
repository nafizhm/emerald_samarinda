@extends('admin.layout')
@section('page_title', 'Pembayaran')
@push('styles')
<link rel="stylesheet" href="{{ asset('template/assets/css/payment-list.css') }}?v={{ filemtime(public_path('template/assets/css/payment-list.css')) }}">
@endpush
@section('content')
<div class="dashboard-page payment-list-page" id="payment-list-page" data-url="{{ route('pembayaran.index') }}">
    <div class="dashboard-intro"><div><p class="dashboard-eyebrow">Pembayaran kavling</p><h1>Pembayaran</h1><p>Pantau progres dan sisa pembayaran. Buka pelanggan untuk melihat cicilan atau mencatat pembayaran.</p></div><a class="dashboard-action" href="{{ route('pelanggan-jatuh-tempo.index') }}"><i class="bi bi-calendar-event"></i>Pelanggan jatuh tempo</a></div>
    <div class="payment-summary" role="group" aria-label="Ringkasan seluruh pelanggan aktif">
        <button type="button" class="payment-summary-card active" data-status="" aria-pressed="true"><span>Seluruh pelanggan<i class="bi bi-people"></i></span><strong id="summary-total">{{ $summary['total'] }}</strong><small>Lihat semua pembayaran</small></button>
        <button type="button" class="payment-summary-card" data-status="Terhutang" aria-pressed="false"><span>Belum lunas<i class="bi bi-wallet2"></i></span><strong id="summary-outstanding">{{ $summary['outstanding'] }}</strong><small>Sisa total <b id="summary-balance">Rp {{ number_format($summary['balance'], 0, ',', '.') }}</b></small></button>
        <button type="button" class="payment-summary-card" data-status="JatuhTempo" aria-pressed="false"><span>Perlu ditagih<i class="bi bi-calendar-event"></i></span><strong id="summary-due">{{ $summary['due'] }}</strong><small>Cicilan sudah jatuh tempo</small></button>
        <button type="button" class="payment-summary-card" data-status="Lunas" aria-pressed="false"><span>Sudah lunas<i class="bi bi-check-circle"></i></span><strong id="summary-settled">{{ $summary['settled'] }}</strong><small>Pembayaran sudah selesai</small></button>
    </div>
    <section class="dashboard-panel payment-list-panel" aria-labelledby="payment-list-title">
        <div class="panel-heading"><div><h2 id="payment-list-title">Daftar pembayaran pelanggan</h2><p>Pelanggan yang jatuh tempo ditampilkan lebih dulu. Ringkasan di atas mencakup seluruh pelanggan aktif.</p></div><button type="button" class="dashboard-action" id="payment-refresh" aria-label="Muat ulang daftar pembayaran"><i class="bi bi-arrow-clockwise"></i>Perbarui</button></div>
        <div class="payment-filters" role="search"><div class="payment-search"><i class="bi bi-search"></i><input type="search" id="payment-search" placeholder="Cari nama, kode pelanggan, kavling, atau proyek..." aria-label="Cari pelanggan atau kavling"></div><div><label for="payment-status">Status pembayaran</label><select class="form-select" id="payment-status"><option value="">Semua status</option><option value="Terhutang">Belum lunas</option><option value="JatuhTempo">Sudah jatuh tempo</option><option value="Lunas">Lunas</option></select></div><div><label for="payment-location">Proyek</label><select class="form-select" id="payment-location"><option value="">Semua proyek</option>@foreach($locations as $location)<option value="{{ $location->id }}">{{ $location->nama_kavling }}</option>@endforeach</select></div><div><label for="payment-method">Jenis pembelian</label><select class="form-select" id="payment-method"><option value="">Semua jenis</option>@foreach($methods as $method)<option value="{{ $method }}">{{ $method === 'Cash Keras' ? 'Cash' : $method }}</option>@endforeach</select></div><button type="button" class="payment-reset" id="payment-reset" hidden>Reset filter</button></div>
        <div class="payment-list-content"><div class="payment-list-error" id="payment-list-error" role="alert" hidden>Daftar belum dapat dimuat. Klik Perbarui untuk mencoba kembali.</div><table class="payment-list-table table" id="payment-list-table"><thead><tr><th>Pelanggan</th><th>Kavling / proyek</th><th>Jenis pembelian</th><th>Progres bayar</th><th>Sisa pembayaran</th><th>Status pembayaran</th><th></th></tr></thead><tbody></tbody></table></div>
    </section>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/payment-list.js') }}?v={{ filemtime(public_path('js/payment-list.js')) }}"></script>
@endpush
