<!DOCTYPE html>
<html lang="en">
@php
    use Illuminate\Support\Facades\DB;

    $namaPerusahaan = DB::table('konfigurasi')->value('nama_perusahaan');

    $favicon = DB::table('konfigurasi_media')->where('jenis_data', 'fav icon')->value('nama_file');

    $logo = DB::table('konfigurasi_media')->where('jenis_data', 'logo website')->value('nama_file');
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $namaPerusahaan ?? 'Aplikasi Admin' }}</title>

    <link rel="shortcut icon" href="{{ asset('config_media/' . ($favicon ?? 'default-fav.png')) }}" type="image/x-icon">

    <link rel="stylesheet"
        href="{{ asset('template/assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">

    <link rel="stylesheet" href="{{ asset('template/assets/extensions/choices.js/public/assets/styles/choices.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('template/assets/compiled/css/table-datatable-jquery.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('template/assets/extensions/rater-js/lib/style.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('template/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/compiled/css/iconly.css') }}">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('template/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('template/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('template/plugins/toastr/toastr.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('template/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        body.light .user-info-text .username {
            color: #fff important;
        }

        body.light .user-info-text .role {
            color: #fff !important;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        .preview-wrapper {
            position: relative;
            width: 100%;
        }

        .preview-item-container {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease;
        }

        .preview-item-container:hover {
            transform: scale(1.02);
        }

        .preview-item-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-preview {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: background-color 0.2s;
            line-height: 0;
        }

        .remove-preview i {
            font-weight: 900;
            font-size: 1.1rem;
            -webkit-text-stroke: 1px white;
            margin-top: 1px;
        }

        .remove-preview:hover {
            background-color: #dc2626;
        }

        #main-navbar {
            background-color: white !important;
            color: #333;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.dark #main-navbar {
            background-color: #1e1e2d !important;
            color: #eee;
        }

        body.dark #main-navbar .nav-link {
            color: #ccc;
        }

        body.dark #main-navbar .nav-link.active,
        body.dark #main-navbar .nav-link:hover {
            color: #5ecba1;
        }

        .main-navbar {
            position: relative;
            z-index: 1000;
        }

        .dropdown-menu {
            top: 100% !important;
            left: auto !important;
            right: 0 !important;
        }

        input[readonly] {
            background-color: #e9ecef !important;
            color: #212529 !important;
        }


        @media (max-width: 768px) {
            #main-menu {
                display: none !important;
                flex-direction: column;
                background-color: #fff;
                padding: 10px 0;
                position: absolute;
                top: 60px;
                left: 0;
                right: 0;
            }

            #main-menu.show {
                display: flex !important;
            }

            body.dark #main-menu {
                background-color: #1e1e2d;
            }

            .main-navbar {
                position: relative;
            }
        }

        .fixed-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1030;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .fixed-header-nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1030;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 767.98px) {
            .fixed-header {
                height: 80px;
            }

            .fixed-header-nav {
                top: 80px;
            }
        }

        .rating-star i {
            font-size: 2rem;
            color: gray;
            cursor: pointer;
            margin: 0 15px;
            transition: color 0.1s;
        }

        .rating-star i.active {
            color: gold;
        }

        @media (min-width: 992px) {
            .rating-star i {
                margin: 0 25px;
            }
        }

        .card.border-danger {
            border: 2px solid #dc3545 !important;
        }

        .card.border-danger.shadow {
            box-shadow: 0 0 10px rgba(220, 53, 69, .6) !important;
        }

        #full-width {
            margin: 2rem
        }

        #main-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: 70px;
            background-color: #1e1e2d;
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        body.dark #main-navbar {
            background-color: #1e1e2d;
        }

        body.light #main-navbar {
            background-color: #ffffff;
        }

        body {
            overflow-x: hidden;
            overflow-y: auto !important;
        }

        #main {
            /* margin-top: 60px; */
            min-height: calc(100vh - 60px) !important;
        }

        #sidebar {
            z-index: 100;
            position: relative;
        }

        .notification-dropdown {
            position: fixed !important;
            top: 50px !important;
            right: 50px;
            min-width: 300px;
            max-height: 350px;
            overflow-y: auto;
            transform: translateX(-50px) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.30);
        }

        /* Desktop */
        @media (min-width: 992px) {
            .notification-dropdown {
                transform: translateX(-170px) !important;
                max-height: 500px;
            }
        }

        #profileDropdown {
            display: flex;
            align-items: center;
        }

        #profileDropdown::after {
            margin-left: 5px;
            margin-top: 0;
        }

        #profileDropdown+.dropdown-menu {
            box-shadow:
                0 10px 25px rgba(0, 0, 0, 0.22),
                0 4px 10px rgba(0, 0, 0, 0.18);
        }

        .navbar-shadow {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            background-color: #fff;
        }

        .submenu.tree-submenu {
            position: relative !important;
            padding-left: 15px !important;
        }

        .submenu.tree-submenu .submenu-item {
            position: relative !important;
            padding-left: 10px !important;
            display: flex;
            align-items: center;
        }

        .submenu.tree-submenu .submenu-item::before {
            content: "";
            position: absolute;
            left: 8px;
            top: -12px;
            bottom: 0;
            width: 0;
            border-left: 2px dashed rgba(187, 187, 187, 0.6) !important;
        }

        .submenu.tree-submenu .submenu-item:last-child::before {
            bottom: 10%;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('template/assets/css/sidebar-custom.css') }}?v={{ filemtime(public_path('template/assets/css/sidebar-custom.css')) }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/workspace-custom.css') }}?v={{ filemtime(public_path('template/assets/css/workspace-custom.css')) }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/due-reminder.css') }}?v={{ filemtime(public_path('template/assets/css/due-reminder.css')) }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/admin-pages.css') }}?v={{ filemtime(public_path('template/assets/css/admin-pages.css')) }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/select2-custom.css') }}?v={{ filemtime(public_path('template/assets/css/select2-custom.css')) }}">
    @stack('styles')
    <style>
        /* =====================================================================
           Theme Color Overrides: Forest & Emerald Green Palette
           Sesuai tema eksklusif login (Emerald Samarinda)
           Menjamin keselarasan aksen hijau baik di Light Mode maupun Dark Mode
           ===================================================================== */
        :root,
        html[data-bs-theme="light"],
        body.light {
            --bs-blue: #166b53;
            --bs-primary: #166b53;
            --bs-primary-rgb: 22, 107, 83;
            --bs-primary-text-emphasis: #0b362a;
            --bs-primary-bg-subtle: #e8f5f0;
            --bs-primary-border-subtle: #b7dfd0;
            --bs-link-color: #166b53;
            --bs-link-color-rgb: 22, 107, 83;
            --bs-link-hover-color: #105240;
            --bs-link-hover-color-rgb: 16, 82, 64;
            --accent: #166b53;
        }

        /* Primary Buttons - Light & Dark */
        .btn-primary,
        html[data-bs-theme="dark"] .btn-primary,
        html[data-bs-theme="light"] .btn-primary,
        body.theme-dark .btn-primary,
        body.dark .btn-primary {
            --bs-btn-bg: #166b53 !important;
            --bs-btn-border-color: #166b53 !important;
            --bs-btn-hover-bg: #105240 !important;
            --bs-btn-hover-border-color: #105240 !important;
            --bs-btn-active-bg: #0d4233 !important;
            --bs-btn-active-border-color: #0d4233 !important;
            --bs-btn-disabled-bg: #166b53 !important;
            --bs-btn-disabled-border-color: #166b53 !important;
            --bs-btn-focus-shadow-rgb: 22, 107, 83 !important;
            background-color: #166b53 !important;
            border-color: #166b53 !important;
            color: #fff !important;
        }
        .btn-primary:hover,
        html[data-bs-theme="dark"] .btn-primary:hover,
        html[data-bs-theme="light"] .btn-primary:hover,
        body.theme-dark .btn-primary:hover,
        body.dark .btn-primary:hover {
            background-color: #105240 !important;
            border-color: #105240 !important;
            color: #fff !important;
        }
        .btn-primary:active,
        .btn-primary.active,
        .show > .btn-primary.dropdown-toggle,
        html[data-bs-theme="dark"] .btn-primary:active,
        html[data-bs-theme="dark"] .btn-primary.active {
            background-color: #0d4233 !important;
            border-color: #0d4233 !important;
            color: #fff !important;
        }
        .btn-outline-primary,
        html[data-bs-theme="light"] .btn-outline-primary {
            --bs-btn-color: #166b53 !important;
            --bs-btn-border-color: #166b53 !important;
            --bs-btn-hover-bg: #166b53 !important;
            --bs-btn-hover-border-color: #166b53 !important;
            --bs-btn-active-bg: #166b53 !important;
            --bs-btn-active-border-color: #166b53 !important;
            --bs-btn-focus-shadow-rgb: 22, 107, 83 !important;
            color: #166b53 !important;
            border-color: #166b53 !important;
        }
        .btn-outline-primary:hover,
        .btn-outline-primary:active,
        .btn-outline-primary.active,
        html[data-bs-theme="light"] .btn-outline-primary:hover {
            background-color: #166b53 !important;
            border-color: #166b53 !important;
            color: #fff !important;
        }

        /* Primary Backgrounds, Texts & Badges */
        .bg-primary,
        html[data-bs-theme="dark"] .bg-primary,
        html[data-bs-theme="light"] .bg-primary {
            background-color: #166b53 !important;
        }
        .text-primary,
        html[data-bs-theme="light"] .text-primary {
            color: #166b53 !important;
        }
        .border-primary,
        html[data-bs-theme="dark"] .border-primary,
        html[data-bs-theme="light"] .border-primary {
            border-color: #166b53 !important;
        }
        .badge.bg-light-primary {
            background-color: #e8f5f0 !important;
            color: #166b53 !important;
        }
        .badge.bg-primary,
        html[data-bs-theme="dark"] .badge.bg-primary {
            background-color: #166b53 !important;
            color: #fff !important;
        }

        /* Form Controls, Checkboxes & Switches */
        .form-check-input:checked,
        html[data-bs-theme="dark"] .form-check-input:checked,
        html[data-bs-theme="light"] .form-check-input:checked,
        body.theme-dark .form-check-input:checked,
        body.dark .form-check-input:checked {
            background-color: #166b53 !important;
            border-color: #166b53 !important;
        }
        .form-check-input[type=checkbox]:indeterminate,
        html[data-bs-theme="dark"] .form-check-input[type=checkbox]:indeterminate {
            background-color: #166b53 !important;
            border-color: #166b53 !important;
        }
        .form-check-input:focus,
        html[data-bs-theme="dark"] .form-check-input:focus {
            border-color: #5ea691 !important;
            box-shadow: 0 0 0 0.25rem rgba(22, 107, 83, 0.2) !important;
        }
        .form-control:focus,
        .form-select:focus,
        html[data-bs-theme="dark"] .form-control:focus,
        html[data-bs-theme="dark"] .form-select:focus {
            border-color: #5ea691 !important;
            box-shadow: 0 0 0 0.25rem rgba(22, 107, 83, 0.18) !important;
        }

        /* Pagination, Nav Tabs & Nav Pills */
        .page-item.active .page-link,
        html[data-bs-theme="dark"] .page-item.active .page-link,
        html[data-bs-theme="dark"] .pagination .page-item.active .page-link {
            background-color: #166b53 !important;
            border-color: #166b53 !important;
            color: #fff !important;
        }
        .nav-pills .nav-link.active,
        .nav-pills .show > .nav-link,
        html[data-bs-theme="dark"] .nav-pills .nav-link.active,
        html[data-bs-theme="light"] .nav-pills .nav-link.active {
            background-color: #166b53 !important;
            border-color: #166b53 !important;
            color: #fff !important;
            box-shadow: 0 2px 10px rgba(22, 107, 83, 0.3) !important;
        }
        .nav-tabs .nav-link.active,
        html[data-bs-theme="light"] .nav-tabs .nav-link.active {
            color: #166b53 !important;
        }
        .nav-tabs .nav-link.active:after,
        html[data-bs-theme="light"] .nav-tabs .nav-link.active:after {
            background-color: #166b53 !important;
            box-shadow: 0 2px 5px rgba(22, 107, 83, 0.3) !important;
        }

        /* List Groups, Dropdowns & Progress */
        .list-group-item.active,
        html[data-bs-theme="dark"] .list-group-item.active {
            background-color: #166b53 !important;
            border-color: #166b53 !important;
            color: #fff !important;
        }
        .dropdown-item.active,
        .dropdown-item:active,
        html[data-bs-theme="dark"] .dropdown-item.active,
        html[data-bs-theme="dark"] .dropdown-item:active {
            background-color: #166b53 !important;
            color: #fff !important;
        }
        .progress-bar,
        html[data-bs-theme="dark"] .progress-bar {
            background-color: #166b53 !important;
        }
        .swal2-styled.swal2-confirm {
            background-color: #166b53 !important;
            border-color: #166b53 !important;
        }
        .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: #e8f5f0 !important;
            color: #166b53 !important;
        }

        /* Dark Mode Overrides (Emerald Cerah & Kontras Tinggi) */
        [data-bs-theme="dark"],
        html[data-bs-theme="dark"],
        body.theme-dark,
        body.dark {
            --bs-primary: #5ecba1;
            --bs-primary-rgb: 94, 203, 161;
            --bs-primary-text-emphasis: #a4ebd0;
            --bs-primary-bg-subtle: #1b382e;
            --bs-primary-border-subtle: #2d5646;
            --bs-link-color: #5ecba1;
            --bs-link-hover-color: #7ce3ba;
            --accent: #5ecba1;
        }
        [data-bs-theme="dark"] .badge.bg-light-primary,
        html[data-bs-theme="dark"] .badge.bg-light-primary,
        body.theme-dark .badge.bg-light-primary,
        body.dark .badge.bg-light-primary {
            background-color: #1b382e !important;
            color: #5ecba1 !important;
        }
        [data-bs-theme="dark"] .text-primary,
        html[data-bs-theme="dark"] .text-primary,
        body.theme-dark .text-primary,
        body.dark .text-primary {
            color: #5ecba1 !important;
        }
        [data-bs-theme="dark"] .btn-outline-primary,
        html[data-bs-theme="dark"] .btn-outline-primary,
        body.theme-dark .btn-outline-primary,
        body.dark .btn-outline-primary {
            color: #5ecba1 !important;
            border-color: #5ecba1 !important;
        }
        [data-bs-theme="dark"] .btn-outline-primary:hover,
        html[data-bs-theme="dark"] .btn-outline-primary:hover,
        body.theme-dark .btn-outline-primary:hover,
        body.dark .btn-outline-primary:hover {
            background-color: #5ecba1 !important;
            border-color: #5ecba1 !important;
            color: #112820 !important;
        }
        [data-bs-theme="dark"] .nav-tabs .nav-link.active,
        html[data-bs-theme="dark"] .nav-tabs .nav-link.active {
            color: #5ecba1 !important;
        }
        [data-bs-theme="dark"] .nav-tabs .nav-link.active:after,
        html[data-bs-theme="dark"] .nav-tabs .nav-link.active:after {
            background-color: #5ecba1 !important;
            box-shadow: 0 2px 5px rgba(94, 203, 161, 0.4) !important;
        }
        html[data-bs-theme="dark"] .sidebar-wrapper .menu .submenu .submenu-item.active > a {
            color: #5ecba1 !important;
        }
        [data-bs-theme="dark"] .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: #1b382e !important;
            color: #5ecba1 !important;
        }

        /* Samakan indikator validasi untuk seluruh form admin, termasuk halaman dengan CSS khusus. */
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: var(--bs-form-invalid-border-color) !important;
            padding-right: calc(1.5em + .75rem) !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right calc(.375em + .1875rem) center !important;
            background-size: calc(.75em + .375rem) calc(.75em + .375rem) !important;
        }

        textarea.form-control.is-invalid {
            background-position: top calc(.375em + .1875rem) right calc(.375em + .1875rem) !important;
        }

        .select2.is-invalid .select2-selection,
        .select2-container.is-invalid .select2-selection {
            border-color: var(--bs-form-invalid-border-color) !important;
            box-shadow: 0 0 0 .15rem rgba(220, 53, 69, .15);
        }

        .invalid-feedback {
            color: var(--bs-form-invalid-color);
        }

        .modal-dialog-scrollable .modal-content > form {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        .modal-dialog-scrollable .modal-content > form > .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .modal-dialog-scrollable .modal-content > .modal-body {
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
        }
    </style>
</head>

<body class="{{ session('theme') === 'dark' ? 'theme-dark' : '' }}">

    <script src="{{ asset('template/assets/static/js/initTheme.js') }}"></script>
    <div id="app">
        <div id="sidebar">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo" style="min-width:0">
                            <a href="{{ route('dashboard') }}" class="sidebar-brand" title="{{ $namaPerusahaan ?? 'Kavling Tanah' }}"><img
                                    src="{{ asset('config_media/' . ($logo ?? 'default-logo.png')) }}" alt="Logo"
                                    >
                                <span class="sidebar-brand-copy"><strong>{{ $namaPerusahaan ?: 'Kavling Tanah' }}</strong><small>Manajemen kavling tanah</small></span>
                            </a>
                        </div>
                        <div class="sidebar-toggler x">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i
                                    class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-menu">
                    <ul class="menu">

                        @include('admin.sidebar_navigation')
                    </ul>
                </div>
            </div>
        </div>
        <div id="main">
            @include('admin.topbar')

            @yield('content')

        </div>
        @include('admin.jatuh_tempo_floating')
    </div>

    <script src="{{ asset('template/assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('template/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>

    <script src="{{ asset('template/assets/compiled/js/app.js') }}"></script>
    <script src="{{ asset('template/assets/extensions/chart.js/chart.umd.js') }}"></script>
    {{-- <!-- Need: Apexcharts -->
    <script src="{{ asset('template/assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('template/assets/static/js/pages/dashboard.js') }}"></script> --}}

    <script src="{{ asset('template/assets/extensions/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('template/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('template/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('template/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('template/assets/static/js/pages/datatables.js') }}"></script>


    <script src="{{ asset('template/assets/extensions/choices.js/public/assets/scripts/choices.js') }}"></script>
    {{-- <script src="{{ asset('template/assets/extensions/rater-js/index.js?v=2') }}"></script> --}}
    {{-- <script src="{{ asset('template/assets/static/js/pages/rater-js.js') }}"></script> --}}
    <script src="{{ asset('template/assets/static/js/pages/form-element-select.js') }}"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Toastr -->
    <script src="{{ asset('template/plugins/toastr/toastr.min.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('template/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>



    <script>
        window.audioAlert = new Audio('{{ asset('audio/notification.ogg') }}');
        window.audioSuccess = new Audio('{{ asset('audio/success.mp3') }}');
        window.audio = window.audioAlert;

        $(document).on('input', '.format-number', function() {
            let input = $(this).val().replace(/[^\d]/g, '');
            let formatted = input.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            $(this).val(formatted);
        });

        function logoutConfirm(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan keluar dari sistem.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('refresh.csrf') }}')
                        .then(response => response.json())
                        .then(data => {
                            let form = document.getElementById('logout-form')
                            let tokenField = form.querySelector('input[name="_token"]')
                            if (!tokenField) {
                                tokenField = document.createElement('input')
                                tokenField.type = 'hidden'
                                tokenField.name = '_token'
                                form.appendChild(tokenField)
                            }
                            tokenField.value = data.token
                            form.submit()
                        })
                }
            });
        }
    </script>

    <script src="{{ asset('js/select2_multiple.js') }}?v={{ filemtime(public_path('js/select2_multiple.js')) }}"></script>

    @php
        $searchPlaceholder = trim($__env->yieldContent('search_placeholder')) ?: 'Ketik kata pencarian...';
    @endphp
    <script>window.workspaceSearchPlaceholder = {{ Illuminate\Support\Js::from($searchPlaceholder) }};</script>

    <script src="{{ asset('template/assets/js/admin-pages.js') }}?v={{ filemtime(public_path('template/assets/js/admin-pages.js')) }}"></script>
    @stack('scripts')
    @if (session('success'))
        <div id="workspace-success-message" hidden>{{ session('success') }}</div>
    @endif

</body>

</html>
