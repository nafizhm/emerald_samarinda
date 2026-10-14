<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Cicilan Pelanggan</title>
    <link rel="stylesheet" href="{{ asset('template/assets/extensions/bootstrap-icons/font/bootstrap-icons.css') }}">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            background: #f5f8fd;
        }

        .cicilan-page {
            color: #0f2b67;
            background:
                radial-gradient(circle at top left, #eef4ff 0, transparent 28%),
                linear-gradient(180deg, #f8fbff 0%, #f3f7fc 100%);
            min-height: 100vh;
            padding: 24px;
        }

        .cicilan-hero,
        .cicilan-card {
            background: rgba(255, 255, 255, .96);
            border: 1px solid #e4eaf4;
            border-radius: 16px;
            box-shadow: 0 12px 35px rgba(31, 72, 145, .08);
        }

        .cicilan-hero {
            padding: 24px;
            margin-bottom: 18px;
        }

        .cicilan-title {
            margin: 0 0 6px;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .cicilan-subtitle {
            color: #6f82a8;
            font-size: 14px;
        }

        .cicilan-user {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
        }

        .cicilan-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #dce9ff, #f2f6ff);
            font-weight: 800;
            color: #1958e8;
            border: 1px solid #d8e2f4;
        }

        .cicilan-kpis {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
            margin-top: 20px;
        }

        .cicilan-kpi {
            border: 1px solid #e4eaf4;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            min-height: 100px;
        }

        .cicilan-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 22px;
            flex: 0 0 auto;
        }

        .cicilan-kpi.blue .cicilan-icon { background: #edf4ff; color: #1958e8; }
        .cicilan-kpi.green .cicilan-icon { background: #e8f8ef; color: #16b85f; }
        .cicilan-kpi.orange .cicilan-icon { background: #fff5dc; color: #f4a914; }
        .cicilan-kpi.red .cicilan-icon { background: #ffecef; color: #f44f61; }
        .cicilan-kpi.purple .cicilan-icon { background: #f1ecff; color: #7c4dd7; }

        .cicilan-label {
            font-size: 13px;
            color: #4f6690;
            margin-bottom: 4px;
        }

        .cicilan-value {
            font-size: 22px;
            font-weight: 800;
            color: #0f2b67;
            line-height: 1.15;
        }

        .cicilan-value.money {
            font-size: 17px;
        }

        .cicilan-note {
            color: #6f82a8;
            font-size: 12px;
            margin-top: 4px;
        }

        .cicilan-card {
            padding: 18px;
        }

        .cicilan-toolbar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .cicilan-control {
            height: 38px;
            border: 1px solid #d9e2f1;
            border-radius: 8px;
            padding: 0 12px;
            background: #fff;
            color: #143371;
            min-width: 150px;
        }

        .cicilan-search {
            position: relative;
            flex: 1 1 260px;
        }

        .cicilan-search .cicilan-control {
            width: 100%;
            padding-left: 34px;
        }

        .cicilan-search i {
            position: absolute;
            left: 12px;
            top: 10px;
            color: #6f82a8;
        }

        .cicilan-table-wrap {
            overflow-x: auto;
            border: 1px solid #e4eaf4;
            border-radius: 12px;
        }

        #paymentTable {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            min-width: 1180px;
        }

        #paymentTable th {
            background: #f0f5ff;
            color: #18386f;
            font-size: 12px;
            text-transform: uppercase;
            padding: 12px;
            border-bottom: 1px solid #dce6f5;
            white-space: nowrap;
        }

        #paymentTable td {
            padding: 12px;
            border-bottom: 1px solid #e8edf5;
            vertical-align: middle;
            font-size: 13px;
        }

        #paymentTable tbody tr:hover td {
            background: #f9fbff;
        }

        .center { text-align: center; }
        .amount { text-align: right; white-space: nowrap; }
        .customer-name { font-weight: 800; color: #113578; }
        .customer-meta { margin-top: 5px; color: #58709a; }
        .due-date { font-weight: 700; color: #1b5ce7; }
        .due-sub { font-size: 12px; color: #62779d; margin-top: 4px; }
        .month-number { font-weight: 800; font-size: 18px; color: #0f2b67; }
        .green-text { color: #16b85f; }
        .orange-text { color: #d78b00; }
        .red-text { color: #f44f61; }
        .zero { color: #16b85f; font-weight: 700; }
        .remaining { color: #f44f61; font-weight: 700; }
        .paid-amount { color: #1958e8; font-weight: 700; }

        .progress-info {
            min-width: 110px;
        }

        .progress-value {
            font-weight: 800;
            margin-bottom: 6px;
        }

        .progress-track {
            height: 7px;
            background: #edf2fb;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: #16b85f;
            border-radius: inherit;
        }

        .progress-bar.warning { background: #f4a914; }
        .progress-bar.danger { background: #f44f61; }

        .cicilan-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 999px;
            padding: 7px 10px;
            font-weight: 800;
            font-size: 12px;
            white-space: nowrap;
        }

        .cicilan-badge.lunas { color: #07924a; background: #e8f8ef; }
        .cicilan-badge.tepat { color: #1764d8; background: #e9f2ff; }
        .cicilan-badge.terlambat { color: #ed3047; background: #ffecef; }
        .cicilan-badge.belum { color: #d78b00; background: #fff5dc; }
        .dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }

        button.cicilan-badge {
            border: 0;
            cursor: pointer;
            font-family: inherit;
        }

        button.cicilan-badge:hover {
            filter: brightness(.98);
            box-shadow: 0 6px 14px rgba(244, 79, 97, .16);
        }

        .cicilan-empty {
            display: none;
            text-align: center;
            padding: 22px;
            color: #6f82a8;
        }

        .cicilan-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 14px;
            color: #6f82a8;
            font-size: 13px;
            flex-wrap: wrap;
        }

        .cicilan-legend {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 14px;
            color: #58709a;
            font-size: 13px;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .legend-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
        }

        .fw-bold {
            font-weight: 700;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-start {
            align-items: flex-start;
        }

        .gap-3 {
            gap: 1rem;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .btn {
            height: 38px;
            border: 1px solid #d9e2f1;
            border-radius: 8px;
            padding: 0 14px;
            cursor: pointer;
            background: #fff;
            color: #1958e8;
            font-weight: 700;
        }

        .btn-primary {
            background: #1958e8;
            color: #fff;
            border-color: #1958e8;
        }

        .btn-outline-primary {
            background: #fff;
            color: #1958e8;
            border-color: #1958e8;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            border: 1px solid #f2d58a;
        }

        .alert-warning {
            background: #fff8e6;
            color: #7a5600;
        }

        .cicilan-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 43, 103, .42);
            z-index: 1000;
        }

        .cicilan-modal.show {
            display: flex;
        }

        .cicilan-modal-panel {
            width: min(760px, 100%);
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 24px 60px rgba(15, 43, 103, .24);
            overflow: hidden;
        }

        .cicilan-modal-header,
        .cicilan-modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid #e4eaf4;
        }

        .cicilan-modal-footer {
            border-top: 1px solid #e4eaf4;
            border-bottom: 0;
            justify-content: flex-end;
        }

        .cicilan-modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
        }

        .cicilan-modal-body {
            padding: 18px;
        }

        .modal-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .modal-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
        }

        .modal-field label {
            font-size: 13px;
            font-weight: 800;
            color: #18386f;
        }

        .modal-field input,
        .modal-field textarea {
            border: 1px solid #d9e2f1;
            border-radius: 8px;
            padding: 10px 12px;
            font: inherit;
            color: #143371;
            width: 100%;
        }

        .modal-field textarea {
            min-height: 150px;
            resize: vertical;
        }

        .modal-alert {
            display: none;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 700;
        }

        .modal-alert.success {
            display: block;
            background: #e8f8ef;
            color: #07924a;
        }

        .modal-alert.error {
            display: block;
            background: #ffecef;
            color: #ed3047;
        }

        .btn-close {
            width: 34px;
            padding: 0;
            font-size: 18px;
            line-height: 1;
        }

        @media (max-width: 1200px) {
            .cicilan-kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .cicilan-page {
                padding: 14px;
            }

            .cicilan-kpis {
                grid-template-columns: 1fr;
            }

            .modal-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    @php
        $percent = function ($value) use ($summary) {
            return $summary['total_customer'] > 0 ? number_format(($value / $summary['total_customer']) * 100, 1, ',', '.') . '%' : '0%';
        };
        $statusClass = [
            'Lunas' => 'lunas',
            'Tepat' => 'tepat',
            'Terlambat' => 'terlambat',
            'Belum Bayar' => 'belum',
        ];
        $hasSkipped = array_sum($skipped) > 0;
    @endphp

    <div class="page-content cicilan-page">
        <section class="cicilan-hero">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h1 class="cicilan-title">Statistik Cicilan Pelanggan</h1>
                    <div class="cicilan-subtitle">Ringkasan status pembayaran cicilan kavling</div>
                </div>
                <div class="cicilan-user">
                    <div class="cicilan-avatar">{{ strtoupper(substr(auth()->user()->surname ?? auth()->user()->username ?? 'A', 0, 1)) }}</div>
                    <div>
                        <div class="fw-bold">{{ auth()->user()->surname ?? auth()->user()->username ?? 'Admin' }}</div>
                        <div class="cicilan-note">{{ optional(auth()->user()->role)->role ?? optional(auth()->user()->role)->nama_role ?? 'Admin' }}</div>
                    </div>
                </div>
            </div>

            <div class="cicilan-kpis">
                <div class="cicilan-kpi blue">
                    <div class="cicilan-icon"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="cicilan-label">Total Pelanggan</div>
                        <div class="cicilan-value">{{ number_format($summary['total_customer'], 0, ',', '.') }}</div>
                        <div class="cicilan-note">Data valid</div>
                    </div>
                </div>

                <div class="cicilan-kpi green">
                    <div class="cicilan-icon"><i class="bi bi-check2-circle"></i></div>
                    <div>
                        <div class="cicilan-label">Tepat Bayar</div>
                        <div class="cicilan-value">{{ number_format($summary['tepat'], 0, ',', '.') }}</div>
                        <div class="cicilan-note green-text">{{ $percent($summary['tepat']) }}</div>
                    </div>
                </div>

                <div class="cicilan-kpi orange">
                    <div class="cicilan-icon"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="cicilan-label">Terlambat</div>
                        <div class="cicilan-value">{{ number_format($summary['terlambat'], 0, ',', '.') }}</div>
                        <div class="cicilan-note orange-text">{{ $percent($summary['terlambat']) }}</div>
                    </div>
                </div>

                <div class="cicilan-kpi red">
                    <div class="cicilan-icon"><i class="bi bi-exclamation-circle"></i></div>
                    <div>
                        <div class="cicilan-label">Belum Bayar</div>
                        <div class="cicilan-value">{{ number_format($summary['belum_bayar'], 0, ',', '.') }}</div>
                        <div class="cicilan-note red-text">{{ $percent($summary['belum_bayar']) }}</div>
                    </div>
                </div>

                <div class="cicilan-kpi purple">
                    <div class="cicilan-icon"><i class="bi bi-wallet2"></i></div>
                    <div>
                        <div class="cicilan-label">Total Piutang</div>
                        <div class="cicilan-value money">Rp {{ number_format($summary['total_piutang'], 0, ',', '.') }}</div>
                        <div class="cicilan-note">Belum terbayar</div>
                    </div>
                </div>
            </div>
        </section>

        @if ($hasSkipped)
            <div class="alert alert-warning">
                Data yang dilewati:
                <strong>{{ $skipped['tanpa_kavling'] }}</strong> tanpa kavling,
                <strong>{{ $skipped['tanpa_tanggal_beli'] }}</strong> tanpa tanggal beli,
                <strong>{{ $skipped['tanpa_nilai_tagihan'] }}</strong> tanpa nilai tagihan/harga.
            </div>
        @endif

        <section class="cicilan-card">
            <div class="cicilan-toolbar">
                <select id="locationFilter" class="cicilan-control">
                    <option value="">Semua Lokasi</option>
                    @foreach ($lokasiList as $lokasi)
                        <option value="{{ $lokasi }}">{{ $lokasi }}</option>
                    @endforeach
                </select>

                <select id="statusFilter" class="cicilan-control">
                    <option value="">Semua Status</option>
                    <option value="Lunas">Lunas</option>
                    <option value="Tepat">Tepat</option>
                    <option value="Terlambat">Terlambat</option>
                    <option value="Belum Bayar">Belum Bayar</option>
                </select>

                <input id="monthFilter" class="cicilan-control" type="month" value="{{ now()->format('Y-m') }}">

                <div class="cicilan-search">
                    <i class="bi bi-search"></i>
                    <input id="searchInput" class="cicilan-control" type="search" placeholder="Cari nama / no kavling / no telp...">
                </div>

                <button type="button" class="btn btn-primary" onclick="applyFilter()">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="exportCSV()">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>

            <div class="cicilan-table-wrap">
                <table id="paymentTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pelanggan<br>Lokasi Kavling</th>
                            <th>Tgl Beli</th>
                            <th>Jatuh Tempo<br>Bulan Ini</th>
                            <th>Berjalan</th>
                            <th>Terbayar / Total</th>
                            <th>Belum<br>Terbayar</th>
                            <th>Harga Jual</th>
                            <th>Sudah Dibayar</th>
                            <th>Sisa</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php
                                $badgeClass = $statusClass[$row['status']] ?? 'belum';
                            @endphp
                            <tr data-location="{{ $row['lokasi'] }}"
                                data-status="{{ $row['status'] }}"
                                data-month="{{ $row['jatuh_tempo']->format('Y-m') }}">
                                <td class="center">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="customer-name">{{ $row['nama_customer'] }}</div>
                                    <div class="customer-meta">{{ $row['kode_kavling'] }} - {{ $row['lokasi'] }}</div>
                                    <div class="customer-meta">{{ $row['no_wa'] ?: '-' }}</div>
                                </td>
                                <td class="center">{{ $row['tanggal_beli']->translatedFormat('d M Y') }}</td>
                                <td class="center">
                                    <div class="due-date">{{ $row['jatuh_tempo']->translatedFormat('d M Y') }}</div>
                                    <div class="due-sub">Bulan ke-{{ $row['bulan_berjalan'] }}</div>
                                </td>
                                <td class="center">
                                    <div class="month-number">{{ $row['bulan_berjalan'] }}</div>
                                    <div class="due-sub">bulan</div>
                                </td>
                                <td class="center">
                                    <div class="progress-info">
                                        <div class="progress-value">
                                            {{ $row['bulan_terbayar'] }} / {{ $row['tenor'] ?: $row['bulan_berjalan'] }}
                                        </div>
                                        <div class="progress-track">
                                            <div class="progress-bar {{ $row['progress_bar_class'] }}" style="width: {{ $row['progress_percent'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="center {{ $row['terlambat_kali'] > 0 ? 'red-text' : 'green-text' }}">
                                    <strong>{{ $row['terlambat_kali'] }} kali</strong>
                                </td>
                                <td class="amount">Rp {{ number_format($row['harga_jual'], 0, ',', '.') }}</td>
                                <td class="amount paid-amount">Rp {{ number_format($row['sudah_dibayar'], 0, ',', '.') }}</td>
                                <td class="amount {{ $row['sisa_bayar'] > 0 ? 'remaining' : 'zero' }}">Rp {{ number_format($row['sisa_bayar'], 0, ',', '.') }}</td>
                                <td class="center">
                                    @if ($row['status'] === 'Terlambat')
                                        <button type="button"
                                            class="cicilan-badge {{ $badgeClass }} btn-open-terlambat"
                                            data-nama="{{ e($row['nama_customer']) }}"
                                            data-no-wa="{{ e($row['no_wa']) }}"
                                            data-pesan="{{ e($row['pesan_terlambat']) }}"
                                            data-terlambat="{{ e($row['terlambat_kali']) }}"
                                            data-total="Rp {{ e(number_format($row['total_tunggakan'], 0, ',', '.')) }}">
                                            <span class="dot"></span>{{ $row['status'] }}
                                        </button>
                                    @else
                                        <span class="cicilan-badge {{ $badgeClass }}"><span class="dot"></span>{{ $row['status'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div id="emptyState" class="cicilan-empty">Data tidak ditemukan.</div>
            </div>

            <div class="cicilan-footer">
                <span id="resultCount">Menampilkan {{ $rows->count() }} data</span>
                <span>{{ now()->translatedFormat('d F Y') }}</span>
            </div>

            <div class="cicilan-legend">
                <strong>Keterangan:</strong>
                <span class="legend-item"><span class="legend-dot" style="background:#16b85f"></span>Lunas</span>
                <span class="legend-item"><span class="legend-dot" style="background:#2970e9"></span>Tepat</span>
                <span class="legend-item"><span class="legend-dot" style="background:#f44f61"></span>Terlambat</span>
                <span class="legend-item"><span class="legend-dot" style="background:#f4a914"></span>Belum Bayar</span>
            </div>
        </section>
    </div>

    <div class="cicilan-modal" id="modalTerlambat" aria-hidden="true">
        <div class="cicilan-modal-panel">
            <div class="cicilan-modal-header">
                <h2 class="cicilan-modal-title">Kirim Pesan Keterlambatan</h2>
                <button type="button" class="btn btn-close" id="btnCloseModal" aria-label="Tutup">x</button>
            </div>
            <form id="formKirimTerlambat">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="cicilan-modal-body">
                    <div id="modalAlert" class="modal-alert"></div>

                    <div class="modal-grid">
                        <div class="modal-field">
                            <label for="modalNamaCustomer">Nama Pelanggan</label>
                            <input type="text" id="modalNamaCustomer" name="nama_customer" readonly>
                        </div>
                        <div class="modal-field">
                            <label for="modalNoWa">No Telp / WA</label>
                            <input type="text" id="modalNoWa" name="no_wa">
                        </div>
                    </div>

                    <div class="modal-grid">
                        <div class="modal-field">
                            <label for="modalJumlahTerlambat">Jumlah Terlambat</label>
                            <input type="text" id="modalJumlahTerlambat" readonly>
                        </div>
                        <div class="modal-field">
                            <label for="modalTotalBayar">Total Harus Dibayar</label>
                            <input type="text" id="modalTotalBayar" readonly>
                        </div>
                    </div>

                    <div class="modal-field">
                        <label for="modalPesan">Isi Pesan</label>
                        <textarea id="modalPesan" name="message"></textarea>
                    </div>
                </div>
                <div class="cicilan-modal-footer">
                    <button type="button" class="btn" id="btnCancelModal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnKirimTerlambat">
                        <span class="button-text">Kirim</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const cicilanRows = Array.from(document.querySelectorAll('#paymentTable tbody tr'));
        const modalTerlambat = document.getElementById('modalTerlambat');
        const modalAlert = document.getElementById('modalAlert');

        function applyFilter() {
            const location = document.getElementById('locationFilter').value.toLowerCase();
            const status = document.getElementById('statusFilter').value.toLowerCase();
            const month = document.getElementById('monthFilter').value;
            const keyword = document.getElementById('searchInput').value.toLowerCase().trim();
            let visible = 0;

            cicilanRows.forEach(row => {
                const rowLocation = (row.dataset.location || '').toLowerCase();
                const rowStatus = (row.dataset.status || '').toLowerCase();
                const rowMonth = row.dataset.month || '';
                const text = row.innerText.toLowerCase();

                const match =
                    (!location || rowLocation === location) &&
                    (!status || rowStatus === status) &&
                    (!month || rowMonth === month) &&
                    (!keyword || text.includes(keyword));

                row.style.display = match ? '' : 'none';
                if (match) {
                    visible++;
                }
            });

            document.getElementById('emptyState').style.display = visible ? 'none' : 'block';
            document.getElementById('resultCount').textContent = visible ?
                `Menampilkan ${visible} data hasil filter` :
                'Tidak ada data yang sesuai';
        }

        document.getElementById('searchInput').addEventListener('input', applyFilter);
        document.getElementById('locationFilter').addEventListener('change', applyFilter);
        document.getElementById('statusFilter').addEventListener('change', applyFilter);
        document.getElementById('monthFilter').addEventListener('change', applyFilter);

        function exportCSV() {
            const visibleRows = cicilanRows.filter(row => row.style.display !== 'none');
            const headers = Array.from(document.querySelectorAll('#paymentTable thead th'))
                .map(th => `"${th.innerText.replace(/\n/g, ' ')}"`);

            const data = visibleRows.map(row =>
                Array.from(row.cells).map(td => `"${td.innerText.replace(/\n/g, ' ').replace(/"/g, '""')}"`)
            );

            const csv = [headers, ...data].map(row => row.join(',')).join('\n');
            const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'statistik-cicilan-customer.csv';
            a.click();
            URL.revokeObjectURL(url);
        }

        function closeModalTerlambat() {
            modalTerlambat.classList.remove('show');
            modalTerlambat.setAttribute('aria-hidden', 'true');
            document.getElementById('formKirimTerlambat').reset();
            modalAlert.className = 'modal-alert';
            modalAlert.textContent = '';
            document.getElementById('btnKirimTerlambat').disabled = false;
            document.querySelector('#btnKirimTerlambat .button-text').textContent = 'Kirim';
        }

        document.querySelectorAll('.btn-open-terlambat').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('modalNamaCustomer').value = button.dataset.nama || '';
                document.getElementById('modalNoWa').value = button.dataset.noWa || '';
                document.getElementById('modalJumlahTerlambat').value = `${button.dataset.terlambat || 0} kali`;
                document.getElementById('modalTotalBayar').value = button.dataset.total || 'Rp 0';
                document.getElementById('modalPesan').value = button.dataset.pesan || '';
                modalAlert.className = 'modal-alert';
                modalAlert.textContent = '';
                modalTerlambat.classList.add('show');
                modalTerlambat.setAttribute('aria-hidden', 'false');
            });
        });

        document.getElementById('btnCloseModal').addEventListener('click', closeModalTerlambat);
        document.getElementById('btnCancelModal').addEventListener('click', closeModalTerlambat);
        modalTerlambat.addEventListener('click', event => {
            if (event.target === modalTerlambat) {
                closeModalTerlambat();
            }
        });

        document.getElementById('formKirimTerlambat').addEventListener('submit', async event => {
            event.preventDefault();

            const form = event.currentTarget;
            const button = document.getElementById('btnKirimTerlambat');
            const buttonText = document.querySelector('#btnKirimTerlambat .button-text');
            const formData = new FormData(form);

            button.disabled = true;
            buttonText.textContent = 'Mengirim...';
            modalAlert.className = 'modal-alert';
            modalAlert.textContent = '';

            try {
                const response = await fetch("{{ route('dashboard.statistik-cicilan-customer.kirim-pesan') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': formData.get('_token'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Pesan gagal dikirim.');
                }

                modalAlert.className = 'modal-alert success';
                modalAlert.textContent = result.message || 'Pesan berhasil dikirim.';
                setTimeout(closeModalTerlambat, 900);
            } catch (error) {
                modalAlert.className = 'modal-alert error';
                modalAlert.textContent = error.message || 'Pesan gagal dikirim.';
                button.disabled = false;
                buttonText.textContent = 'Kirim';
            }
        });
    </script>
</body>
</html>
