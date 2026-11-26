@php
    $rupiah = fn($value) => 'Rp ' . number_format($value, 0, ',', '.');
    $next = $schedule['rows']->first(fn($row) => $row['balance'] > 0);
    $tampilkan_jatuh_tempo = in_array($customer->skema_pembayaran, ['Cash Tempo', 'Kredit']);
    $kodeKavlingGabungan = $customer->kavling->pluck('kode_kavling')->filter()->implode(', ') ?: '-';
@endphp

<section class="dashboard-panel installment-overview">
    <div class="installment-overview-top">
        <div><span class="installment-kicker">{{ $customer->lokasi?->nama_kavling ?? 'Lokasi belum tersedia' }} —
                {{ $kodeKavlingGabungan }}</span>
            <h2>{{ $schedule['remaining'] === 0 ? 'Pembayaran sudah lunas' : 'Pantau cicilan, catat pembayaran' }}
            </h2>
            <p>{{ in_array($customer->skema_pembayaran, ['Cash Tempo', 'Kredit']) ? $customer->lama_cicilan . ' bulan / ' . $rupiah($customer->nominal_cicilan) . ' per bulan' : 'Pembayaran berdasarkan nilai kontrak kavling.' }}
            </p>
        </div><span class="installment-contract-status {{ $schedule['remaining'] === 0 ? 'paid' : '' }}"><i
                class="bi {{ $schedule['remaining'] === 0 ? 'bi-check-circle' : 'bi-clock' }}"></i>{{ $schedule['remaining'] === 0 ? 'Lunas' : 'Dalam pembayaran' }}</span>
    </div>
    <div class="installment-metrics">
        <div><span>Nilai kontrak</span><strong>{{ $rupiah($schedule['contract']) }}</strong></div>
        <div><span>Sudah dibayar</span><strong class="installment-green">{{ $rupiah($schedule['paid']) }}</strong>
        </div>
        <div><span>Sisa pembayaran</span><strong>{{ $rupiah($schedule['remaining']) }}</strong></div>
    </div>
    <div class="installment-progress-caption"><span>Progres
            pembayaran</span><strong>{{ $schedule['percent'] }}%</strong></div>
    <div class="installment-progress" role="progressbar" aria-label="Progres pembayaran"
        aria-valuenow="{{ $schedule['percent'] }}" aria-valuemin="0" aria-valuemax="100"><span
            style="width:{{ $schedule['percent'] }}%"></span></div>
</section>
@if (!$schedule['valid'])
    <div class="installment-alert"><i class="bi bi-info-circle"></i>
        <div><strong>Jadwal cicilan belum lengkap</strong>
            <p>Periksa tenor, nominal bulanan, dan tanggal cicilan pertama di data customer. Pembayaran nominal
                tetap tersedia.</p>
        </div>
    </div>
@endif
<section class="dashboard-panel installment-schedule" aria-labelledby="schedule-title">
    <div class="panel-heading">
        <div>
            <h2 id="schedule-title">
                {{ $customer->skema_pembayaran === 'Kredit' ? 'Jadwal cicilan' : 'Rencana pembayaran' }}</h2>
            <p>Pilih tahap pembayaran yang ingin dicatat. Sisa tahap sebelumnya otomatis ikut dihitung.</p>
        </div>
    </div>
    @if ($customer->skema_pembayaran === 'Kredit')
        <div class="installment-toolbar">
            <div class="installment-filter" role="group" aria-label="Filter cicilan"><button type="button"
                    class="active" data-filter="all" aria-pressed="true">Semua bulan</button><button type="button"
                    data-filter="unpaid" aria-pressed="false">Belum selesai</button></div>
            <span>{{ $schedule['rows']->where('balance', 0)->count() }} dari {{ $schedule['rows']->count() }}
                tahap selesai</span>
        </div>
    @endif
    <table class="installment-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Periode</th>
                @if ($tampilkan_jatuh_tempo)
                    <th>Jatuh tempo</th>
                @endif
                <th class="text-end">
                    Nominal</th>
                <th class="text-end">Terbayar</th>
                <th class="text-end">Sisa</th>
                <th>Status</th>
                <th class="text-end">Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedule['rows'] as $row)
                <tr data-month="{{ $row['month'] }}" data-unpaid="{{ $row['balance'] > 0 ? '1' : '0' }}"
                    class="{{ $row['late'] ? 'installment-late-row' : '' }}">
                    <td data-label="No.">{{ $loop->iteration }}</td>
                    <td data-label="Periode"><strong>{{ $row['label'] }}</strong>
                        @if ($next && $next['month'] === $row['month'])
                            <small class="installment-next">Pembayaran berikutnya</small>
                        @endif
                    </td>
                    @if ($tampilkan_jatuh_tempo)
                        <td data-label="Jatuh tempo">
                            {{ $row['date']?->locale('id')->translatedFormat('d M Y') ?? '-' }}</td>
                    @endif
                    <td class="text-end" data-label="Nominal">
                        {{ $rupiah($row['amount']) }}</td>
                    <td class="text-end installment-green" data-label="Terbayar">{{ $rupiah($row['paid']) }}</td>
                    <td class="text-end fw-semibold" data-label="Sisa">{{ $rupiah($row['balance']) }}</td>
                    <td><span
                            class="installment-status {{ $row['balance'] === 0 ? 'paid' : ($row['late'] ? 'late' : 'pending') }}">{{ $row['status'] }}</span>
                    </td>
                    <td class="text-end">
                        @if ($row['balance'] > 0 && $canPay && $schedule['valid'])
                            <button type="button" class="installment-pay select-installment"
                                data-month="{{ $row['month'] }}" data-amount="{{ $row['pay_through'] }}"
                                data-label="{{ $row['month'] === 0 ? $row['label'] : 'Bayar sampai ' . $row['label'] }}">{{ $row['month'] === 0 ? 'Bayar DP' : 'Bayar sampai bulan ' . $row['month'] }}<i
                                    class="bi bi-arrow-right"></i></button>
                        @elseif($row['balance'] === 0)
                            <i class="bi bi-check2 installment-green" aria-label="Sudah lunas"></i>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $tampilkan_jatuh_tempo ? 8 : 7 }}" class="installment-empty">Belum ada jadwal
                        pembayaran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <p class="installment-footnote"><i class="bi bi-info-circle"></i>Pembayaran sebagian diperhitungkan otomatis.
        Cicilan terakhir menyesuaikan sisa nilai kontrak.</p>
</section>
<section class="dashboard-panel installment-history" aria-labelledby="history-title">
    <div class="panel-heading">
        <div>
            <h2 id="history-title">Riwayat pembayaran</h2>
            <p>Transaksi yang telah dicatat, termasuk DP dan pembayaran beberapa bulan sekaligus.</p>
        </div>
        <div class="installment-actions">
            @if ($canPay && $schedule['remaining'] > 0)
                <button type="button" class="btn btn-primary btn-sm" id="add-transaction"><i
                        class="bi bi-plus-lg"></i> Tambah Transaksi</button>
            @endif
            <span class="dashboard-date">
                {{ $history->count() }} transaksi</span>
        </div>
    </div>
    <table class="installment-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th class="text-end">Nominal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $payment)
                <tr>
                    <td data-label="No.">{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->tanggal)->locale('id')->translatedFormat('d M Y') }}
                    </td>
                    <td><strong>{{ $payment->keterangan ?: 'Pembayaran kavling' }}</strong><small>{{ $payment->no_kwitansi ?: 'Transaksi #' . $payment->id }}</small>
                    </td>
                    <td class="text-end fw-semibold">{{ $rupiah($payment->nominal) }}</td>
                    <td class="text-nowrap">{!! $payment->aksi !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="installment-empty">Belum ada pembayaran tercatat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
