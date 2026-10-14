<?php

return [
    ['id' => 2, 'title' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'bi bi-grid-fill', 'section' => null],
    ['id' => 3, 'title' => 'Denah Penjualan', 'route' => 'st-penjualan.index', 'icon' => 'bi bi-map-fill', 'section' => 'Operasional'],
    ['id' => 5, 'title' => 'Pelanggan', 'icon' => 'bi bi-people-fill', 'section' => 'Operasional', 'children' => [
        'customer.index' => 'Data Pelanggan',
        'upload-file.index' => 'Dokumen Pelanggan',
        'arsip-customer.index' => 'Arsip Pelanggan',
    ]],
    ['id' => 27, 'title' => 'Pembayaran', 'route' => 'pembayaran.index', 'icon' => 'bi bi-credit-card', 'section' => 'Operasional'],
    ['id' => 28, 'title' => 'Legalitas', 'route' => 'legalitas.index', 'icon' => 'bi bi-folder-fill', 'section' => 'Operasional'],
    ['id' => 4, 'title' => 'Keuangan', 'icon' => 'bi bi-cash-stack', 'section' => 'Administrasi', 'children' => [
        'pemasukan.index' => 'Pemasukan',
        'pengeluaran.index' => 'Pengeluaran',
        'mutasi-saldo.index' => 'Transfer Rekening',
        'piutang.index' => 'Piutang',
        'hutang.index' => 'Utang',
        'laporan-arus-kas.index' => 'Laporan Arus Kas',
        'bank.index' => 'Rekening Bank',
        'kategori-transaksi.index' => 'Kategori Transaksi',
    ]],
    ['id' => 7, 'title' => 'Data Referensi', 'icon' => 'bi bi-database-fill', 'section' => 'Administrasi', 'children' => [
        'lokasi-kavling.index' => 'Lokasi Kavling',
        'kavling.index' => 'Data Kavling',
        'marketing.index' => 'Tim Marketing',
        'notaris.index' => 'Notaris',
        'list-penjualan.index' => 'Tahapan Penjualan',
    ]],
    ['id' => 8, 'title' => 'Pengaturan', 'icon' => 'bi bi-gear-fill', 'section' => 'Sistem', 'children' => [
        'pengaturan-profile.index' => 'Profil & Media',
        'pengaturanLanding.index' => 'Halaman Publik',
        'pengguna.index' => 'Pengguna',
        'hak-akses.index' => 'Hak Akses',
    ]],
    ['id' => 38, 'title' => 'WhatsApp', 'icon' => 'bi bi-whatsapp', 'section' => 'Sistem', 'children' => [
        'pengaturan-koneksi.index' => 'Koneksi WhatsApp',
        'template-pesan.index' => 'Template Pesan',
    ]],
];
