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

        body.dark .select2-container--default .select2-selection--single {
            background-color: #1e1e2d;
            color: #fff;
            border: 1px solid #444;
        }

        body.dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff;
        }

        body.dark .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #aaa;
        }

        body.dark .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #fff transparent transparent transparent;
        }

        body.dark .select2-container--default .select2-results__options {
            padding-left: 0 !important;
            margin: 0;
        }

        body.dark .select2-container--default .select2-results__option {
            text-align: left;
            padding: 6px 12px;
            color: #fff;
        }

        body.dark .select2-container--default .select2-results__option--highlighted[aria-selected],
        body.dark .select2-container--default .select2-results__option--highlighted[aria-selected]:hover {
            background-color: #444;
            color: #fff;
        }

        body.dark .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: #333;
            color: #fff;
        }

        body.dark .select2-dropdown {
            background-color: #2c2f33;
            color: #fff;
            border-color: #444;
            z-index: 9999;
        }

        body.dark .select2-container--default .select2-selection--multiple {
            background-color: #1e1e2d;
            border: 1px solid #444;
            min-height: 38px;
            position: relative;
            padding-right: 45px;
        }

        body.dark .select2-container--default.select2-container--focus .select2-selection--multiple {
            border: 1px solid #444;
            outline: none;
        }

        body.dark .select2-container--default .select2-selection--multiple::after {
            border-top-color: #ffffff;
        }

        body.dark .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #2c2f33;
            border: 1px solid #444;
            color: #fff;
            padding: 2px 6px;
            margin-top: 5px;
            display: inline-flex;
            flex-direction: row-reverse;
            align-items: center;
        }

        body.dark .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: static !important;
            color: #ccc;
            margin-right: 0;
            margin-left: 6px;
            border-right: none;
            border-left: 1px solid #444;
            padding-right: 0;
            padding-left: 6px;
            float: none;
        }

        body.dark .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #fff;
            background-color: transparent;
        }

        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 6px 12px;
            font-size: 1rem;
            background-color: #fff;
        }

        .select2-container .select2-selection__arrow {
            display: none;
        }

        .select2-container .select2-selection--single:focus {
            outline: none;
        }

        .select2-container .select2-selection__rendered {
            line-height: 30px;
        }

        .select2-container--default .select2-selection--single {
            position: relative;
            padding-right: 36px;
        }

        .select2-container--default .select2-selection--single::after {
            content: '';
            position: absolute;
            top: 50%;
            right: 12px;
            width: 0;
            height: 0;
            pointer-events: none;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 6px solid #6c757d;
            transform: translateY(-50%);
        }

        body.dark .select2-container--default .select2-selection--single::after {
            border-top-color: #ffffff;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            margin: 0;
            padding: 0;
            z-index: 2;
        }

        .select2-container--default .select2-selection--single {
            padding-right: 52px;
        }

        .select2-container--default .select2-selection--multiple {
            position: relative;
            padding-right: 45px;
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding-left: 8px;
            font-size: 1rem;
            background-color: #fff;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border: 1px solid #ced4da;
            outline: none;
        }

        .select2-container--default .select2-selection--multiple::after {
            content: '';
            position: absolute;
            top: 50%;
            right: 12px;
            width: 0;
            height: 0;
            pointer-events: none;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 6px solid #6c757d;
            transform: translateY(-50%);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__clear {
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            margin: 0;
            padding: 0;
            z-index: 2;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            color: #495057;
            border-radius: 0.2rem;
            padding: 2px 6px;
            margin-top: 5px;
            display: inline-flex;
            flex-direction: row-reverse;
            align-items: center;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: static !important;
            color: #495057;
            margin-right: 0;
            margin-left: 6px;
            border-right: none;
            border-left: 1px solid #ced4da;
            padding-right: 0;
            padding-left: 6px;
            float: none;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #212529;
            background-color: transparent;
        }

        .select2-container--default .select2-selection--multiple .select2-search--inline {
            display: inline-flex;
            align-items: center;
            width: 100%;
        }

        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
            margin-top: 6px;
            line-height: inherit;
            width: 100% !important;
            box-sizing: border-box;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice~.select2-search--inline {
            display: none !important;
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
            color: #0d6efd;
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
    @stack('styles')
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
        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

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

    <script src="{{ asset('js/select2_multiple.js') }}"></script>

    <script src="{{ asset('template/assets/js/admin-pages.js') }}?v={{ filemtime(public_path('template/assets/js/admin-pages.js')) }}"></script>
    @stack('scripts')
    @if (session('success'))
        <div id="workspace-success-message" hidden>{{ session('success') }}</div>
    @endif

</body>

</html>
