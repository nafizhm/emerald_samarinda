@php $dueCount = app(\App\Services\JatuhTempoService::class)->rows()->count(); @endphp
<div class="due-notification" id="due-notification">
    <section class="due-popup" id="due-popup" role="dialog" aria-labelledby="due-popup-title" hidden>
        <header><div><h2 id="due-popup-title">Pengingat pembayaran</h2><p id="due-popup-summary">Maksimal 10 pelanggan dengan jatuh tempo terlama.</p></div><button type="button" id="due-popup-close" aria-label="Tutup pengingat"><i class="bi bi-x-lg"></i></button></header>
        <div id="due-popup-list" class="due-popup-list" aria-live="polite"><p class="due-popup-state">Memuat pelanggan...</p></div>
        <footer><a href="{{ route('pelanggan-jatuh-tempo.index') }}">Lihat semua pelanggan <i class="bi bi-arrow-right"></i></a></footer>
    </section>
    <button type="button" class="due-floating" id="due-floating" title="{{ $dueCount }} pelanggan jatuh tempo" aria-label="{{ $dueCount }} pelanggan jatuh tempo. Buka popup pengingat." aria-expanded="false" aria-controls="due-popup" data-notification-url="{{ route('pelanggan-jatuh-tempo.notification') }}">
        <i class="bi bi-whatsapp" aria-hidden="true"></i><span class="due-floating-badge {{ $dueCount === 0 ? 'is-empty' : '' }}" id="due-floating-badge" aria-hidden="true">{{ $dueCount > 99 ? '99+' : $dueCount }}</span>
    </button>
</div>
@push('scripts')
<script src="{{ asset('js/due-notification.js') }}?v={{ filemtime(public_path('js/due-notification.js')) }}"></script>
@endpush
