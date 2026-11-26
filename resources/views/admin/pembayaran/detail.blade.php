@extends('admin.layout')
@section('page_title', 'Pembayaran Kavling')
@push('styles')
    <link rel="stylesheet"
        href="{{ asset('template/assets/css/installments.css') }}?v={{ filemtime(public_path('template/assets/css/installments.css')) }}">
@endpush
@section('content')
    @php
        $rupiah = fn($value) => 'Rp ' . number_format($value, 0, ',', '.');
        $next = $schedule['rows']->first(fn($row) => $row['balance'] > 0);
        $tampilkan_jatuh_tempo = $customer->skema_pembayaran === 'Cash Tempo';
        $kodeKavlingGabungan = $customer->kavling->pluck('kode_kavling')->filter()->implode(', ') ?: '-';
    @endphp
    <div class="dashboard-page installment-page" id="installment-page"
        data-url="{{ route('pembayaran.bayar-cicilan', $customer->id) }}" data-paid="{{ $schedule['paid'] }}"
        data-remaining="{{ $schedule['remaining'] }}">
        <div class="dashboard-intro">
            <div>
                <p class="dashboard-eyebrow">Pembayaran kavling</p>
                <h1>{{ $customer->nama_lengkap }}</h1>
                <p>{{ $customer->kode_customer }} <span class="installment-separator">/</span>
                    {{ $customer->skema_pembayaran }} <span class="installment-separator">/</span>
                    <strong class="text-primary" id="active-kavling-code">{{ $kodeKavlingGabungan }}</strong></p>
            </div>
            <div class="installment-actions"><a href="{{ route('pembayaran.index') }}" class="dashboard-action"><i
                        class="bi bi-arrow-left"></i>Daftar pembayaran</a><a
                    href="{{ route('customer.cetak-rekap', $customer->id) }}" class="dashboard-action" target="_blank"
                    rel="noopener"><i class="bi bi-printer"></i>Cetak rekap</a></div>
        </div>

        <div id="installment-content-wrapper">
            @include('admin.pembayaran.partials.content')
        </div>
    </div>
    <div class="modal fade text-left" id="transaction-modal" tabindex="-1" role="dialog"
        aria-labelledby="transaction-modal-title" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"
        data-focus="false">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title white" id="transaction-modal-title">Tambah Transaksi</h5><button
                        type="button" class="close white" data-bs-dismiss="modal" aria-label="Tutup"><i
                            data-feather="x"></i></button>
                </div>
                <div class="modal-body">
                    <form id="transaction-form"
                        data-url="{{ route('pembayaran.tambah-transaksi-cicilan', $customer->id) }}"
                        data-create-url="{{ route('pembayaran.tambah-transaksi-cicilan', $customer->id) }}"
                        data-method="POST">@csrf
                        <div class="row mb-3 align-items-center"><label for="tanggal"
                                class="col-sm-3 col-form-label">Tanggal</label>
                            <div class="col-sm-4"><input type="date" name="tanggal" id="tanggal"
                                    class="form-control" value="{{ now('Asia/Jakarta')->toDateString() }}"></div>
                        </div>
                        <div class="row mb-3 align-items-center"><label for="id_kategori_transaksi"
                                class="col-sm-3 col-form-label">Kategori Transaksi</label>
                            <div class="col-sm-6"><select name="id_kategori_transaksi" id="id_kategori_transaksi"
                                    class="form-select">
                                    <option value="">Pilih kategori pemasukan</option>
                                    @foreach ($kategoriPemasukan as $kategori)
                                        <option value="{{ $kategori->id }}">{{ $kategori->kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center"><label for="nominal"
                                class="col-sm-3 col-form-label">Nominal</label>
                            <div class="col-sm-5">
                                <div class="input-group"><span class="input-group-text">Rp</span><input type="text"
                                        name="nominal" id="nominal" class="form-control format-number"
                                        inputmode="numeric"></div>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center"><label for="lampiran"
                                class="col-sm-3 col-form-label">Lampiran</label>
                            <div class="col-sm-8"><input type="file" name="lampiran" id="lampiran"
                                    class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf"><small
                                    class="text-muted">JPG, PNG, WebP, atau PDF. Maksimal 2 MB.</small>
                                <div class="img-thumbnail border mt-2 d-flex align-items-center justify-content-center"
                                    id="transaction-preview"
                                    style="max-width: 140px; height: 150px; background-color: #f8f9fa; border: 1px solid #dee2e6; overflow: hidden;">
                                    <span style="color: #6c757d;">Tidak ada File</span></div>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center"><label for="keterangan"
                                class="col-sm-3 col-form-label">Keterangan <span
                                    class="text-muted">(opsional)</span></label>
                            <div class="col-sm-9">
                                <textarea name="keterangan" id="keterangan" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><span
                            class="button-text">Batal</span></button><button type="submit" form="transaction-form"
                        class="btn btn-primary ms-1" id="transaction-save"><span
                            class="spinner-border spinner-border-sm me-2 d-none" role="status"
                            aria-hidden="true"></span><span class="button-text">Simpan</span></button></div>
            </div>
        </div>
    </div>
    <div class="modal fade text-left" id="installment-modal" tabindex="-1" role="dialog"
        aria-labelledby="installment-modal-title" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"
        data-focus="false">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content ">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title white" id="installment-modal-title">Catat pembayaran</h5>
                    <button type="button" class="close white" data-bs-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="installment-form">@csrf<input type="hidden" name="mode" id="payment-mode"><input
                            type="hidden" name="month" id="payment-month"><input type="hidden" name="expected_paid"
                            id="payment-expected-paid" value="{{ $schedule['paid'] }}">
                        <p id="payment-selection"></p>
                        <div class="installment-payment-summary"><span>Nominal yang dicatat</span><strong
                                id="payment-total"></strong><small id="payment-after"></small></div>
                        <div id="payment-error" class="alert alert-danger" role="alert" hidden></div>
                        <div class="mb-3" id="manual-amount-group" hidden><label for="payment-amount"
                                class="form-label">Nominal pembayaran (Rp)</label><input type="number" min="1"
                                max="{{ $schedule['remaining'] }}" step="1" class="form-control" name="amount"
                                id="payment-amount"></div>
                        <div class="mb-3"><label for="payment-date" class="form-label">Tanggal pembayaran</label><input
                                type="date" name="date" id="payment-date" class="form-control"
                                value="{{ now('Asia/Jakarta')->toDateString() }}"
                                max="{{ now('Asia/Jakarta')->toDateString() }}" required></div>
                        <div class="mb-3"><label for="payment-bank" class="form-label">Rekening penerima</label><select
                                class="form-select" name="bank_id" id="payment-bank" required>
                                <option value="">Pilih rekening</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}" @selected($customer->id_bank == $bank->id)>
                                        {{ $bank->nama }}{{ $bank->no_rek ? ' / ' . $bank->no_rek : '' }}</option>
                                @endforeach
                            </select></div>
                        <div class="mb-3"><label for="payment-note" class="form-label">Catatan <span
                                    class="text-muted">(opsional)</span></label>
                            <textarea name="note" id="payment-note" class="form-control" rows="2" maxlength="500"></textarea>
                        </div>
                        <div class="mb-3"><label for="payment-proof" class="form-label">Bukti pembayaran <span
                                    class="text-muted">(opsional)</span></label><input type="file" name="proof"
                                id="payment-proof" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf"><small
                                class="text-muted">JPG, PNG, WebP, atau PDF. Maksimal 2 MB.</small></div>
                        <p class="installment-confirm-note">Simpan setelah dana benar-benar diterima. Jadwal dan sisa
                            pembayaran akan diperbarui.</p>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <span class="button-text">Batal</span>
                    </button>
                    <button type="submit" form="installment-form" class="btn btn-primary ms-1" id="payment-save">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                            aria-hidden="true"></span>
                        <span class="button-text">Simpan pembayaran</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/installments.js') }}?v={{ filemtime(public_path('js/installments.js')) }}"></script>
@endpush
