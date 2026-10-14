@php
    $account = auth()->user();
    $accountName = $account?->surname ?: ($account?->username ?: 'Pengguna');
    $accountRole = $account?->role?->role ?: 'Pengguna';
@endphp
<header class="app-topbar">
    <div class="topbar-context">
        <button type="button" class="topbar-icon burger-btn d-xl-none" aria-label="Buka navigasi"><i class="bi bi-list"></i></button>
        <a class="topbar-workspace" id="workspace-home-link" href="{{ route('beranda') }}" title="Buka beranda">Kavling Tanah</a><span class="topbar-divider">/</span>
        <span class="topbar-page">@yield('page_title', 'Ruang kerja')</span>
    </div>
    <div class="topbar-actions">
        <button type="button" class="topbar-search" data-bs-toggle="modal" data-bs-target="#workspace-menu-search" aria-label="Cari menu" title="Cari menu (Ctrl + K)"><i class="bi bi-search"></i><span>Cari menu</span><kbd>Ctrl K</kbd></button>
        <button type="button" class="topbar-icon" id="topbar-theme" title="Ganti tampilan" aria-label="Ganti tampilan terang atau gelap"><i class="bi bi-moon"></i></button>
        <span class="topbar-separator"></span>
        <div class="dropdown">
            <button type="button" class="topbar-account" id="account-menu" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="account-avatar">{{ mb_strtoupper(mb_substr($accountName, 0, 1)) }}</span>
                <span class="account-copy"><strong>{{ $accountName }}</strong><small>{{ $accountRole }}</small></span>
                <i class="bi bi-chevron-down account-chevron"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end account-dropdown" aria-labelledby="account-menu">
                <div class="px-3 py-2"><small class="text-muted">Profil pengguna</small><strong class="d-block mt-1">{{ $accountName }}</strong><span class="small text-muted d-block">{{ $account?->username }}</span>@if($account?->email)<span class="small text-muted d-block text-break">{{ $account->email }}</span>@endif<span class="account-role">{{ $accountRole }}</span></div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('beranda') }}"><i class="bi bi-house me-2"></i>Beranda &amp; akses cepat</a>
                <button type="button" class="dropdown-item text-danger" onclick="logoutConfirm(event)"><i class="bi bi-box-arrow-right me-2"></i>Keluar dari akun</button>
            </div>
        </div>
        <button type="button" class="topbar-icon topbar-logout" title="Keluar dari akun" aria-label="Keluar dari akun" onclick="logoutConfirm(event)"><i class="bi bi-box-arrow-right"></i></button>
    </div>
</header>
@include('admin.menu_search')
<form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display:none">@csrf</form>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const button = document.getElementById('topbar-theme');
    function syncTheme() {
        const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        document.body.classList.toggle('dark', dark);
        document.body.classList.toggle('light', !dark);
        button.querySelector('i').className = dark ? 'bi bi-sun' : 'bi bi-moon';
        button.title = dark ? 'Gunakan tampilan terang' : 'Gunakan tampilan gelap';
        button.setAttribute('aria-label', button.title);
        button.setAttribute('aria-pressed', dark ? 'true' : 'false');
    }
    button.addEventListener('click', function() {
        const theme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);
        syncTheme();
    });
    new MutationObserver(syncTheme).observe(document.documentElement, {attributes:true, attributeFilter:['data-bs-theme']});
    syncTheme();
});
</script>
@endpush
