<!DOCTYPE html>
<html lang="id">
@php
    use Illuminate\Support\Facades\DB;
    $namaPerusahaan = DB::table('konfigurasi')->value('nama_perusahaan');
    $favicon = DB::table('konfigurasi_media')->where('jenis_data', 'fav icon')->value('nama_file');
    $logo = DB::table('konfigurasi_media')->where('jenis_data', 'logo website')->value('nama_file');
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#153c32">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk | {{ $namaPerusahaan ?? 'Manajemen Kavling' }}</title>
    <link rel="shortcut icon" href="{{ asset('config_media/' . ($favicon ?? 'default-fav.png')) }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/css/login-exclusive.css') }}">
</head>
<body style="--landscape: url('{{ asset('assets/img/login-kavling-premium.png') }}')">
    <main class="login-layout">
        <section class="landscape-panel" aria-label="Manajemen penjualan kavling tanah">
            <div class="brand">
                <span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 40 40" fill="none"><path d="m6 26 14-8 14 8-14 8L6 26Zm0-9 14-8 14 8M6 21l14-8 14 8" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></span>
                <div><strong>{{ $namaPerusahaan ?? 'Manajemen Kavling' }}</strong><span>LAND & PROPERTY MANAGEMENT</span></div>
            </div>
            <div class="hero-copy">
                <div class="eyebrow"><span></span> SETIAP LAHAN, SEBUAH PELUANG</div>
                <h1>Kelola lahan.<br>Bangun <em>masa depan.</em></h1>
                <p>Satu ruang untuk mengelola kavling, menjalin hubungan dengan pelanggan, dan mengembangkan penjualan Anda.</p>
                <div class="feature-list"><span>Inventori kavling</span><span>Penjualan & pembayaran</span><span>Legalitas</span></div>
            </div>
            <div class="landscape-footer"><span class="location-mark" aria-hidden="true">⌖</span><span>Langkah besar dimulai dari pengelolaan yang tepat.</span><span class="edition">PROPERTY MANAGEMENT</span></div>
        </section>
        <section class="form-panel" aria-labelledby="login-title">
            <div class="panel-top"><span class="portal-label"><span></span> PORTAL MANAJEMEN</span><span class="private-label">AKSES INTERNAL</span></div>
            <div class="form-content">
                @if ($logo)
                    <img class="company-logo" src="{{ asset('config_media/' . $logo) }}" alt="Logo {{ $namaPerusahaan ?? 'perusahaan' }}">
                @else
                    <div class="logo-fallback" aria-hidden="true">⌖</div>
                @endif
                <div class="welcome-label">SELAMAT DATANG KEMBALI</div>
                <h2 id="login-title">Masuk ke ruang kerja.</h2>
                <p class="form-description">Kelola setiap peluang properti Anda dengan lebih terarah.</p>
                <div id="login-message" class="login-message" role="alert" tabindex="-1" hidden></div>
                @if (session('success'))
                    <div class="session-message" role="status">{{ session('success') }}</div>
                @endif
                <form id="formLogin" action="{{ route('admin.loginPost') }}" method="POST">
                    @csrf
                    <div class="form-field">
                        <label for="username">Username</label>
                        <div class="input-shell"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20v-2a7 7 0 0 1 14 0v2"/></svg><input id="username" name="username" type="text" placeholder="Masukkan username Anda" autocomplete="username" required aria-describedby="username-error" value="{{ old('username') }}"></div>
                        <span id="username-error" class="field-error" aria-live="polite"></span>
                    </div>
                    <div class="form-field">
                        <label for="password">Password</label>
                        <div class="input-shell"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V6a4 4 0 0 1 8 0v4M12 14v3"/></svg><input id="password" name="password" type="password" placeholder="Masukkan password Anda" autocomplete="current-password" required aria-describedby="password-error"><button class="password-toggle" type="button" aria-label="Tampilkan password" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button></div>
                        <span id="password-error" class="field-error" aria-live="polite"></span>
                    </div>
                    <div class="access-note"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z"/><path d="m8 12 3 3 5-5"/></svg><span>Gunakan akun yang diberikan administrator Anda.</span></div>
                    <button id="submitBtn" class="submit-button" type="submit"><span class="button-text">Masuk ke dashboard</span><span class="button-arrow" aria-hidden="true">↗</span><span class="spinner" aria-hidden="true" hidden></span></button>
                </form>
                <div class="help-note">Kesulitan mengakses akun?<br><span>Hubungi administrator perusahaan Anda.</span></div>
            </div>
            <footer class="form-footer"><span>&copy; {{ date('Y') }} {{ $namaPerusahaan ?? 'Manajemen Kavling' }}</span><span>Ruang kerja properti Anda</span></footer>
        </section>
    </main>
    <script src="{{ asset('template/plugins/jquery/jquery.min.js') }}"></script>
    <script>
        $(function () {
            const form = document.getElementById('formLogin');
            const submit = document.getElementById('submitBtn');
            const message = document.getElementById('login-message');
            const password = document.getElementById('password');
            document.querySelector('.password-toggle').addEventListener('click', function () {
                const visible = password.type === 'password';
                password.type = visible ? 'text' : 'password';
                this.setAttribute('aria-pressed', String(visible));
                this.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
            });
            function showMessage(text) { message.textContent = text; message.hidden = false; message.focus(); }
            function setBusy(busy) {
                submit.disabled = busy;
                form.setAttribute('aria-busy', String(busy));
                submit.querySelector('.spinner').hidden = !busy;
                submit.querySelector('.button-arrow').hidden = busy;
                submit.querySelector('.button-text').textContent = busy ? 'Sedang masuk...' : 'Masuk ke dashboard';
            }
            $(form).on('submit', async function (event) {
                event.preventDefault();
                if (submit.disabled) return;
                message.hidden = true;
                form.querySelectorAll('input').forEach(input => input.removeAttribute('aria-invalid'));
                form.querySelectorAll('.field-error').forEach(error => error.textContent = '');
                setBusy(true);
                try {
                    const csrf = await $.get('{{ route('refresh.csrf') }}');
                    document.querySelector('meta[name="csrf-token"]').content = csrf.token;
                    form.querySelector('input[name="_token"]').value = csrf.token;
                    await $.ajax({ url: form.action, method: 'POST', data: new FormData(form), contentType: false, processData: false, headers: { 'X-CSRF-TOKEN': csrf.token, 'Accept': 'application/json' } });
                    window.location.href = @json(route('dashboard'));
                } catch (xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        let firstInvalid;
                        Object.entries(xhr.responseJSON.errors).forEach(([name, errors]) => {
                            const input = form.elements.namedItem(name);
                            const error = document.getElementById(name + '-error');
                            if (input && error) { input.setAttribute('aria-invalid', 'true'); error.textContent = errors[0]; firstInvalid ??= input; }
                        });
                        if (firstInvalid) firstInvalid.focus();
                        else showMessage('Data login belum sesuai. Silakan periksa kembali.');
                    } else {
                        showMessage(xhr.status === 429 ? 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.' : xhr.status === 419 ? 'Sesi telah berakhir. Muat ulang halaman lalu coba lagi.' : 'Belum dapat terhubung. Periksa koneksi Anda dan coba lagi.');
                    }
                    setBusy(false);
                }
            });
        });
    </script>
</body>
</html>
