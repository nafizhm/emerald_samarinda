@extends('admin.layout')
@section('page_title', 'Transaksi pembelian baru')
@push('styles')
    <link rel="stylesheet"
        href="{{ asset('template/assets/css/customer-custom.css') }}?v={{ filemtime(public_path('template/assets/css/customer-custom.css')) }}">
@endpush
@section('content')
    <div class="dashboard-page customer-page purchase-page">

        <div class="dashboard-intro">
            <div>
                <h1>Tambah Customer Baru</h1>
                <p>Form tambah data customer kavling baru.</p>
            </div>
            <a class="dashboard-action" href="{{ route('customer.index') }}">
                <i class="bi bi-arrow-left"></i>
                Kembali ke customer
            </a>
        </div>

        <section class="section">
            <div class="card">
                <form id="formData" class="purchase-form" novalidate>
                    @csrf
                    <div class="card-body">
                        <div id="purchase-errors" class="purchase-errors" role="alert" tabindex="-1" hidden></div>
                        <section class="purchase-section" id="buyer-section">
                            <div class="purchase-section-heading"><span>01</span>
                                <div>
                                    <h2>Identitas customer</h2>
                                    <p>Lengkapi data customer. Kolom bertanda * wajib diisi.</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="tgl_terima" class="col-sm-2 col-form-label">Tanggal transaksi <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-2">
                                    <input type="date" name="tgl_terima" id="tgl_terima" aria-required="true"
                                        class="form-control"
                                        value="{{ \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d') }}">
                                </div>
                                <label for="nama_lengkap" class="col-sm-2 col-form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-6">
                                    <input type="text" name="nama_lengkap" id="nama_lengkap" autocomplete="name"
                                        aria-required="true" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="no_ktp" class="col-sm-2 col-form-label">No. KTP <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-4">
                                    <input type="text" inputmode="numeric" name="no_ktp" id="no_ktp"
                                        class="form-control">
                                </div>
                                <label for="no_ktp_p" class="col-sm-2 col-form-label">No. KTP Pasangan</label>
                                <div class="col-sm-4">
                                    <input type="text" inputmode="numeric" name="no_ktp_p" id="no_ktp_p"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tempat_lahir" class="col-sm-2 col-form-label">Tempat Lahir <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-4">
                                    <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control">
                                </div>

                                <label for="tgl_lahir" class="col-sm-2 col-form-label">Tanggal Lahir <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-4">
                                    <input type="date" name="tgl_lahir" id="tgl_lahir" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="no_telp" class="col-sm-2 col-form-label">No. Telp / WA <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-4">
                                    <input type="text" inputmode="numeric" name="no_telp" id="no_telp"
                                        autocomplete="tel" aria-required="true" class="form-control">
                                </div>
                                <label for="jenis_kelamin" class="col-sm-2 col-form-label">Jenis Kelamin</label>
                                <div class="col-sm-4">
                                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-control select-jk">
                                        <option value=""></option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="email" class="col-sm-2 col-form-label">Email</label>
                                <div class="col-sm-4">
                                    <input type="email" name="email" id="email" autocomplete="email"
                                        class="form-control">
                                </div>
                                <label for="npwp" class="col-sm-2 col-form-label">NPWP</label>
                                <div class="col-sm-4">
                                    <input type="text" name="npwp" id="npwp" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="alamat" class="col-sm-2 col-form-label">Alamat KTP <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <textarea name="alamat" id="alamat" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="alamat_domisili" class="col-sm-2 col-form-label">Alamat Domisili</label>
                                <div class="col-sm-10">
                                    <textarea name="alamat_domisili" id="alamat_domisili" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="row mb-5">
                                <label for="pekerjaan" class="col-sm-2 col-form-label">Pekerjaan</label>
                                <div class="col-sm-4">
                                    <input type="text" name="pekerjaan" id="pekerjaan" class="form-control">
                                </div>
                            </div>

                        </section>
                        <section class="purchase-section" id="unit-section">
                            <div class="purchase-section-heading"><span>02</span>
                                <div>
                                    <h2>Unit &amp; transaksi</h2>
                                    <p>Pilih lokasi terlebih dahulu. Hanya kavling yang tersedia dapat dipilih.</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="id_lokasi" class="col-sm-2 col-form-label">Lokasi Kavling <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-4">
                                    <select name="id_lokasi" id="id_lokasi" class="form-control select-lokasi">
                                        <option value=""></option>
                                        @foreach ($lokasi as $l)
                                            <option value="{{ $l->id }}">{{ $l->nama_kavling }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label for="id_kavling" class="col-sm-2 col-form-label">Blok / Kode Kavling <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-4">
                                    <select name="id_kavling[]" id="id_kavling"
                                        class="form-control select-multiple select-kavling" multiple="multiple"
                                        placeholder="Pilih Kavling">
                                    </select>
                                    <small class="text-muted">Bisa pilih lebih dari satu</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="hrg_jual" class="col-sm-2 col-form-label">Harga Jual</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" class="form-control format-number" id="hrg_jual"
                                            name="hrg_jual" readonly>
                                    </div>
                                </div>
                                <label for="discount" class="col-sm-2 col-form-label">Diskon</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" class="form-control format-number" id="discount"
                                            name="discount">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="id_marketing" class="col-sm-2 col-form-label">Marketing</label>
                                <div class="col-sm-4">
                                    <select name="id_marketing" id="id_marketing" class="form-control select-marketing">
                                        <option value=""></option>
                                        @foreach ($marketing as $m)
                                            <option value="{{ $m->id }}">{{ $m->nama_marketing }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="ket_cashback" class="col-sm-2 col-form-label">Keterangan</label>
                                <div class="col-sm-10">
                                    <textarea name="ket_cashback" id="ket_cashback" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="skema_pembayaran" class="col-sm-2 col-form-label">Skema Pembayaran <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-4">
                                    <select name="skema_pembayaran" id="skema_pembayaran"
                                        class="form-control select-jenis-pembelian">
                                        <option value=""></option>
                                        <option value="Cash Tempo">Cash Tempo</option>
                                        <option value="Cash Keras">Cash Keras</option>
                                        <option value="Kredit">Kredit</option>
                                    </select>
                                    <small class="text-muted">Menentukan skema pembayaran: cicilan sendiri, tunai keras,
                                        atau melalui bank.</small>
                                </div>
                            </div>

                        </section>
                        <section class="purchase-section" id="payment-section">
                            <div class="purchase-section-heading"><span>03</span>
                                <div>
                                    <h2>Rencana pembayaran</h2>
                                    <p>Cash Tempo mencatat pembayaran awal, Cash Keras mencatat pelunasan, dan Kredit
                                        mencatat
                                        DP serta cicilan.</p>
                                </div>
                            </div>
                            <div class="purchase-summary" aria-live="polite">
                                <div><span>Kavling dipilih</span><strong id="summary-units">0 unit</strong></div>
                                <div><span>Harga total</span><strong id="summary-price">Rp 0</strong></div>
                                <div><span>Setelah diskon</span><strong id="summary-net">Rp 0</strong></div>
                                <div><span>Sisa pembayaran</span><strong id="summary-balance">Rp 0</strong></div>
                            </div>
                            <p id="purchase-payment-hint" class="purchase-hint">Pilih skema pembayaran untuk melengkapi
                                rencana pembayaran.</p>
                            <!-- CASH TEMPO -->
                            <hr class="hr-transaksi" style="display: none;">
                            <div id="trx_booking" style="display: none;">
                                <div class="row mb-3">
                                    <label for="nominal_cicilan" class="col-sm-2 col-form-label">Nominal Cicilan
                                        <span class="text-danger">*</span></label>
                                    <div class="col-sm-4">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control format-number" id="nominal_cicilan"
                                                name="nominal_cicilan">
                                        </div>
                                    </div>
                                    <label for="lama_cicilan" class="col-sm-2 col-form-label">Lama Cicilan
                                        <span class="text-danger">*</span></label>
                                    <div class="col-sm-4">
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="lama_cicilan"
                                                name="lama_cicilan" min="1" step="1">
                                            <span class="input-group-text">Bulan</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="tgl_jatuh_tempo" class="col-sm-2 col-form-label">Tanggal Jatuh Tempo
                                        <span class="text-danger">*</span></label>
                                    <div class="col-sm-4">
                                        <input type="date" name="tgl_jatuh_tempo" id="tgl_jatuh_tempo"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="card-footer purchase-actions"><span>Periksa unit dan nominal sebelum menyimpan
                            transaksi.</span><a href="{{ route('customer.index') }}"
                            class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                                aria-hidden="true"></span>
                            <span class="button-text">Simpan transaksi</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select-jk').select2({
                dropdownParent: $('body'),
                width: '100%',
                placeholder: 'Pilih Jenis Kelamin',
                minimumResultsForSearch: Infinity,
                allowClear: true,
            });
            $('.select-lokasi').select2({
                dropdownParent: $('body'),
                width: '100%',
                placeholder: 'Pilih Lokasi',
                allowClear: true,
            });
            $('.select-kavling').select2({
                dropdownParent: $('body'),
                width: '100%',
                placeholder: 'Pilih Kavling',
                allowClear: true,
            });
            $('.select-marketing').select2({
                dropdownParent: $('body'),
                width: '100%',
                placeholder: 'Pilih Marketing',
                allowClear: true,
            });
            $('.select-jenis-pembelian').select2({
                dropdownParent: $('body'),
                width: '100%',
                placeholder: 'Pilih Skema Pembayaran',
                allowClear: true,
            });

            if ($('body').hasClass('dark')) {
                $('.select2-container').addClass('select2-dark');
            }

            function hideAllTransactionForms() {
                $('#trx_booking').hide().find('input').prop('disabled', true);
                $('.hr-transaksi').hide();
            }

            $('#skema_pembayaran').on('change', function() {
                let selected = $(this).val();
                hideAllTransactionForms();
                switch (selected) {
                    case 'Cash Tempo':
                        $('#trx_booking').prev('.hr-transaksi').show();
                        $('#trx_booking').show().find('input').prop('disabled', false);
                        break;
                }
            });

            hideAllTransactionForms();

            const routeGetKavling = "{{ route('customer.getKavling', ':id') }}";
            const routeGetHarga = "{{ route('customer.getHargaKavling', ':ids') }}";

            function parseNumber(value) {
                return parseInt(String(value).replace(/\./g, '').replace(/[^\d]/g, '')) || 0;
            }

            function formatNumber(value) {
                return new Intl.NumberFormat('id-ID').format(value || 0);
            }

            function hitungCicilan() {
                // Fungsi ini tidak lagi dipakai
            }

            let unitRequest = null;
            let priceRequest = null;
            let priceReady = false;
            const unitSelect = $('#id_kavling').prop('disabled', true);
            const unitHint = $(
                '<small class="purchase-hint d-block" role="status">Pilih lokasi untuk melihat kavling tersedia.</small>'
            ).insertAfter(unitSelect.next('.select2'));

            function updateSummary() {
                const price = parseNumber($('#hrg_jual').val());
                const net = Math.max(0, price - parseNumber($('#discount').val()));
                const type = $('#skema_pembayaran').val();
                $('#summary-units').text((unitSelect.val() || []).length + ' unit');
                $('#summary-price').text('Rp ' + formatNumber(price));
                $('#summary-net').text('Rp ' + formatNumber(net));
                $('#summary-balance').text('Rp ' + formatNumber(net));
                $('#purchase-payment-hint').text(type === 'Cash Keras' ?
                    'Seluruh harga setelah diskon akan dicatat sebagai piutang pelunasan.' : type ===
                    'Cash Tempo' ?
                    'Rencana cicilan akan dicatat. Pembayaran dilakukan lewat menu Pembayaran.' :
                    type === 'Kredit' ?
                    'Data kredit akan dicatat. Pencairan dilakukan melalui bank.' :
                    'Pilih skema pembayaran untuk melengkapi rencana pembayaran.');
                $('#submitBtn').data('price-ready', priceReady);
            }

            $('#id_lokasi').on('change', function() {
                if (unitRequest) unitRequest.abort();
                if (priceRequest) priceRequest.abort();
                const locationId = $(this).val();
                priceReady = false;
                unitSelect.empty().prop('disabled', true).trigger('change');
                unitHint.text(locationId ? 'Memuat kavling tersedia...' :
                    'Pilih lokasi untuk melihat kavling tersedia.');
                if (!locationId) return;
                unitRequest = $.get(routeGetKavling.replace(':id', locationId))
                    .done(function(data) {
                        data.forEach(item => unitSelect.append(new Option(item.kode_kavling, item.id)));
                        unitSelect.prop('disabled', !data.length).trigger('change');
                        unitHint.text(data.length ? data.length +
                            ' kavling tersedia. Anda dapat memilih lebih dari satu unit.' :
                            'Tidak ada kavling tersedia di lokasi ini. Pilih lokasi lain.');
                    }).fail(function(xhr, status) {
                        if (status !== 'abort') unitHint.text(
                            'Kavling gagal dimuat. Pilih ulang lokasi untuk mencoba kembali.');
                    });
            });

            unitSelect.on('change', function() {
                if (priceRequest) priceRequest.abort();
                priceReady = false;
                $('#hrg_jual').val('');
                updateSummary();
                const ids = $(this).val() || [];
                if (!ids.length) return;
                unitHint.text('Menghitung harga unit...');
                priceRequest = $.get(routeGetHarga.replace(':ids', ids.join(',')))
                    .done(function(data) {
                        $('#hrg_jual').val(data.formatted);
                        priceReady = true;
                        unitHint.text(ids.length + ' unit dipilih. Harga total sudah diperbarui.');
                        updateSummary();
                    }).fail(function(xhr, status) {
                        if (status !== 'abort') unitHint.text(
                            'Harga gagal dimuat. Pilih ulang kavling sebelum menyimpan.');
                    });
            });
            $('#formData').on('input change', 'input, select', updateSummary);
            updateSummary();
        });

        $('#formData').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#submitBtn');
            if (submitBtn.prop('disabled')) return;
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            let url = '{{ route('customer.store') }}';
            let method = 'POST';

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let formData = new FormData(this);
            formData.append('_method', method);

            $('#purchase-errors').prop('hidden', true).empty();

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    localStorage.setItem('customerSuccess', 'Transaksi pembelian berhasil disimpan.');
                    window.location.href = "{{ route('customer.index') }}";
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        if (typeof audio !== 'undefined') audio.play().catch(() => {});
                        toastr.error("Ada inputan yang salah!", "GAGAL!", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });

                        let errors = xhr.responseJSON.errors || {};
                        let firstInvalid = null;
                        $.each(errors, function(key, val) {
                            const field = key.split('.')[0];
                            const input = $(`[name="${field}"] , [name="${field}[]"]`).first();
                            if (!input.length) return;

                            input.addClass('is-invalid').attr('aria-invalid', 'true');
                            const select2 = input.next('.select2');
                            select2.addClass('is-invalid');
                            const group = input.closest('[class*="col-"]').first();
                            group.find(`.invalid-feedback[data-field="${field}"]`).remove();
                            group.append(
                                `<span class="invalid-feedback d-block" data-field="${field}" role="alert"><b>${val[0]}</b></span>`
                            );
                            if (!firstInvalid) firstInvalid = input;
                        });
                        if (firstInvalid) firstInvalid.trigger('focus');
                    }

                    if (xhr.status !== 422) {
                        $('#purchase-errors').prop('hidden', false).text(xhr.responseJSON?.message ||
                                'Transaksi belum tersimpan. Periksa koneksi dan coba kembali.')[0]
                            .focus();
                    }
                    spinner.addClass('d-none');
                    btnText.text('Simpan transaksi');
                    submitBtn.prop('disabled', false);
                }
            });
        });
    </script>
@endpush
