@extends('admin.layout')
@section('page_title', 'Detail Pembayaran')
@section('content')
    <style>
        .table tr td,
        .table tr th {
            padding: 6px 10px;
            font-size: 14px;
            line-height: 1.2;
        }

        #previewKwitansiPdf {
            width: 100%;
            height: 520px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .btn-action-xs {
            padding: 2px 6px;
            font-size: 11px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .action-pemasukan {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            flex-wrap: nowrap;
        }
    </style>

    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Pembayaran</p><h1>Detail Pembayaran</h1><p class="workspace-description">Tinjau tagihan, pembayaran, dan catatan transaksi pelanggan.</p></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mt-2">
                        <div class="col col-7">
                            <div class="row mb-3">
                                <label class="control-label col-sm-4">Nama Pelanggan</label>
                                <div class="col-sm-7">
                                    <input name="nama_lengkap" value="{{ $customer->nama_lengkap }}" class="form-control"
                                        type="text" disabled>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="control-label col-sm-4">Lokasi Kavling</label>
                                <div class="col-sm-7">
                                    <input name="lokasi_blok" class="form-control" type="text"
                                        value="{{ $customer->lokasiKavling->nama_kavling ?? '-' }} # {{ $kodeKavlingGabungan }}"
                                        disabled>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="control-label col-sm-4">Jenis Pembayaran</label>
                                <div class="col-sm-7">
                                    <input name="ket_cashback" id="ket_cashback" class="form-control" type="text"
                                        value="{{ $customer->jenis_pembelian }}" disabled>
                                </div>
                            </div>
                        </div>

                        <!-- Awal Kanan -->
                        <div class="col col-5">
                            <div class="row mb-3">
                                <label class="control-label col-sm-4">Total Tagihan</label>
                                <div class="col-sm-5">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input class="form-control text-right" type="text" id="total_tagihan_all"
                                            value="{{ number_format($customer->piutangs->sum('nominal'), 0, ',', '.') }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="control-label col-sm-4">Jumlah Bayar</label>
                                <div class="col-sm-5">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input class="form-control text-right" type="text" id="jumlah_bayar_all"
                                            value="{{ number_format($customer->piutangs->sum('terbayar'), 0, ',', '.') }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="control-label col-sm-4">Sisa Bayar</label>
                                <div class="col-sm-5">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input class="form-control text-right" type="text" id="sisa_bayar_all"
                                            value="{{ number_format($customer->piutangs->sum('sisa_bayar'), 0, ',', '.') }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('customer.cetak-rekap', $customer->id) }}" target="_blank"
                                class="btn btn-sm btn-primary">

                                <i class="fa fa-print"></i> Cetak Rekap
                            </a>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <h5>- Tagihan - </h5>
                                </div>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalTagihan"><i class="fa fa-home"></i>
                                    Tambah Tagihan</button>
                            </div>

                            <table class="table table-bordered table-tagihan">
                                <thead>
                                    <tr class="table-primary">
                                        <th width="30px">No</th>
                                        <th>Jenis Tagihan</th>
                                        <th>Nominal</th>
                                        <th width="100px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" align="right"><b>Total Tagihan</b></td>
                                        <td align="right" id="total-tagihan"><b>Rp. 0</b></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <h5>- Pemasukan -</h5>
                                </div>
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalPemasukan"><i class="fa fa-money-bill"></i>
                                    Tambah Pemasukan</button>
                            </div>

                            <table class="table table-bordered table-pemasukan">
                                <thead>
                                    <tr class="table-success">
                                        <th width="30px">No</th>
                                        <th>Tanggal</th>
                                        <th>Jenis Pembayaran</th>
                                        <th>Jenis</th>
                                        <th width="150px">Nominal</th>
                                        <th width="130px">Aksi</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" align="right"><b>Total Pemasukan</b></td>
                                        <td align="right"><b id="total-pemasukan">Rp. 0</b></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade text-left" id="modalTagihan" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-focus="false">

        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="myModalLabel160">Tagihan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <form id="formTagihan">
                        @csrf
                        <input type="hidden" id="primary_id" name="primary_id" value="{{ $customer->id }}">
                        <div class="row mb-3">
                            <label class="control-label col-sm-4">Kategori Transaksi</label>
                            <div class="col-sm-8">
                                <select name="id_kategori" id="id_kategori"
                                    class="form-control select-kategori-transaksi-tagihan">
                                    <option value=""></option>
                                    @foreach ($kategoriTransaksiTagihan as $data)
                                        <option value="{{ $data->id }}">{{ $data->kategori }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label">Deskripsi Tagihan</label>
                            <div class="col-sm-8">
                                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label">Nominal</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="nominal" id="nominal"
                                        class="form-control format-number">
                                </div>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary ms-1" id="submitBtnTagihan">
                        <span class="spinner-border spinner-border-sm mx-1 d-none" role="status"
                            aria-hidden="true"></span>
                        <span class="button-text">Simpan</span>
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade text-left" id="modalPemasukan" tabindex="-1" role="dialog"
        aria-labelledby="modalFormLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"
        data-focus="false">

        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="myModalLabel160">Pemasukan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <form id="formPemasukan">
                        @csrf
                        <input type="hidden" id="primary_id" name="primary_id" value="{{ $customer->id }}">
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Tanggal Pembayaran</label>
                            <div class="col-sm-4">
                                <input name="tanggal_pembayaran" id="tanggal_pembayaran" class="form-control"
                                    type="date" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="control-label col-sm-3">Nama Pelanggan</label>
                            <div class="col-sm-9">
                                <input name="nama_lengkap" value="{{ $customer->nama_lengkap }}" class="form-control"
                                    type="text" disabled>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Lokasi Kavling</label>
                            <div class="col-sm-4">
                                <input name="lokasi_b" value="{{ $customer->lokasiKavling->nama_kavling }}"
                                    class="form-control" type="text" disabled>
                            </div>
                            <label class="col-sm-1 col-form-label">Blok</label>
                            <div class="col-sm-4">
                                <input name="kode_kavling" value="{{ $kodeKavlingGabungan }}" class="form-control"
                                    type="text" disabled>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Kategori Transaksi</label>
                            <div class="col-sm-6">
                                <select name="id_kategori_transaksi" id="id_kategori_transaksi"
                                    class="form-control select-kategori-transaksi-pemasukan">
                                    <option value=""></option>
                                    @foreach ($kategoriTransaksiPemasukan as $data)
                                        <option value="{{ $data->id }}">{{ $data->kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3" id="wrapper_tagihan" style="display: none;">
                            <label class="col-sm-3 col-form-label">Tagihan</label>
                            <div class="col-sm-6">
                                <select name="id_tagihan" id="id_tagihan" class="form-control select-tagihan">
                                    <option value=""></option>
                                    @foreach ($piutang as $data)
                                        <option value="{{ $data->id }}">{{ $data->kategori->kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Cara Bayar</label>
                            <div class="col-sm-5">
                                <select name="id_metode" id="id_metode" class="form-control select-cara-bayar">
                                    <option value=""></option>
                                    @foreach ($metodeBayar as $bayar)
                                        <option value="{{ $bayar->id }}">{{ $bayar->jenis_bayar }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Nominal</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input name="nominal_bayar" id="nominal_bayar" class="form-control format-number"
                                        type="text">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Keterangan</label>
                            <div class="col-sm-9">
                                <input name="keterangan_pembayaran" id="keterangan_pembayaran" class="form-control"
                                    type="text">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Bukti Pembayaran</label>
                            <div class="col-sm-8">
                                <input type="file" name="file" id="file"
                                    accept=".jpg, .jpeg, .png, .webp, .pdf">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-8">
                                <div class="img-thumbnail mb-2 d-flex align-items-center justify-content-center"
                                    id="previewFoto"
                                    style="max-width: 140px; height: 150px; background-color: #f8f9fa; border: 1px solid #dee2e6; overflow: hidden;">
                                    <span style="color: #6c757d;">Tidak ada File</span>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary ms-1" id="submitBtnPemasukan">
                        <span class="spinner-border spinner-border-sm mx-1 d-none" role="status"
                            aria-hidden="true"></span>
                        <span class="button-text">Simpan</span>
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade text-left" id="modalKirimKwitansi" tabindex="-1" role="dialog"
        aria-labelledby="modalKirimKwitansiLabel" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="modalKirimKwitansiLabel">Kirim Kwitansi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <form id="formKirimKwitansi">
                        @csrf
                        <input type="hidden" id="kirim_pdf_url" name="pdf_url">

                        <div class="row mb-3 align-items-center">
                            <label for="kirim_nama_customer" class="col-sm-3 col-form-label">Nama Pelanggan</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="kirim_nama_customer"
                                    name="nama_customer" readonly>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="kirim_no_wa" class="col-sm-3 col-form-label">No WA</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" id="kirim_no_wa" name="no_wa">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="kirim_isi_pesan" class="col-sm-3 col-form-label">Isi Pesan</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="kirim_isi_pesan" name="isi_pesan" rows="5"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-sm-3 col-form-label">File PDF</label>
                            <div class="col-sm-9">
                                <iframe id="previewKwitansiPdf" src=""></iframe>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" form="formKirimKwitansi" class="btn btn-success" id="submitBtnKirimKwitansi">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                            aria-hidden="true"></span>
                        <span class="button-text">Kirim</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

        $(document).on('click', '.btn-cetak-pemasukan', function() {
            let id = $(this).data('id');
            $('.btn-kirim-pemasukan[data-id="' + id + '"]').removeClass('d-none');
        });

        $('#modalKirimKwitansi').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);
            let pdfUrl = button.attr('data-pdf-url') || '';

            $('#kirim_nama_customer').val(button.attr('data-nama') || '');
            $('#kirim_no_wa').val(button.attr('data-no-wa') || '');
            $('#kirim_isi_pesan').val(button.attr('data-pesan') || '');
            $('#kirim_pdf_url').val(pdfUrl);
            $('#previewKwitansiPdf').attr('src', pdfUrl ? pdfUrl + '?v=' + Date.now() : '');
        });

        $('#modalKirimKwitansi').on('hidden.bs.modal', function() {
            $('#formKirimKwitansi')[0].reset();
            $('#previewKwitansiPdf').attr('src', '');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let submitBtn = $('#submitBtnKirimKwitansi');
            submitBtn.find('.spinner-border').addClass('d-none');
            submitBtn.find('.button-text').text('Kirim');
            submitBtn.prop('disabled', false);
        });

        $('#formKirimKwitansi').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#submitBtnKirimKwitansi');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Mengirim...');
            submitBtn.prop('disabled', true);

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            $.ajax({
                url: '{{ route('pembayaran.kirim-kwitansi') }}',
                method: 'POST',
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $('#modalKirimKwitansi').modal('hide');
                        audio.play();
                        toastr.success(response.message || "Pesan dan PDF berhasil dikirim.",
                            "BERHASIL", {
                                progressBar: true,
                                timeOut: 3500,
                                positionClass: "toast-bottom-right",
                            });
                    }
                },
                error: function(xhr) {
                    audio.play();

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        toastr.error("Ada inputan yang salah!", "GAGAL!", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });

                        $.each(xhr.responseJSON.errors, function(key, val) {
                            let input = $('[name="' + key + '"]');
                            input.addClass('is-invalid');
                            input.parent().find('.invalid-feedback').remove();
                            input.parent().append(
                                '<span class="invalid-feedback" role="alert"><strong>' +
                                val[0] + '</strong></span>'
                            );
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message || "Gagal mengirim kwitansi.",
                            "GAGAL!", {
                                progressBar: true,
                                timeOut: 3500,
                                positionClass: "toast-bottom-right",
                            });
                    }
                },
                complete: function() {
                    spinner.addClass('d-none');
                    btnText.text('Kirim');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        $('#file').on('change', function() {
            const file = this.files[0];
            const previewDiv = $('#previewFoto');

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewDiv.html(
                        `<img src="${e.target.result}" style="max-width: 100%; max-height: 100%;">`);
                };
                reader.readAsDataURL(file);
            } else {
                previewDiv.html('<span style="color: #6c757d;">Tidak ada File</span>');
            }
        });

        $(document).ready(function() {
            if ($('body').hasClass('dark')) {
                $('.select2-container').addClass('select2-dark');
            }

            $('.select-kategori-transaksi-tagihan').select2({
                dropdownParent: $('#modalTagihan'),
                width: '100%',
                placeholder: 'Pilih Kategori Transaksi',
                allowClear: true
            });

            $('.select-kategori-transaksi-pemasukan').select2({
                dropdownParent: $('#modalPemasukan'),
                width: '100%',
                placeholder: 'Pilih Kategori Transaksi',
                allowClear: true
            });

            $('.select-tagihan').select2({
                dropdownParent: $('#modalPemasukan'),
                width: '100%',
                placeholder: 'Pilih Tagihan',
                allowClear: true
            });

            $('.select-cara-bayar').select2({
                dropdownParent: $('#modalPemasukan'),
                width: '100%',
                placeholder: 'Pilih Metode Bayar',
                allowClear: true
            });

            function toggleTagihan() {
                let kategori = $('#id_kategori_transaksi').val();

                if (kategori == '102') {
                    $('#wrapper_tagihan').show();
                } else {
                    $('#wrapper_tagihan').hide();
                    $('#id_tagihan').val('').trigger('change');
                }
            }

            toggleTagihan();

            $('#id_kategori_transaksi').on('change', function() {
                toggleTagihan();
            });

        });

        $(function() {
            var customerId = '{{ $customer->id }}';
            var url = "{{ route('pembayaran.detail-tagihan', ['id' => '__id__']) }}".replace('__id__', customerId);

            $('.table-tagihan').DataTable({
                processing: false,
                serverSide: true,
                paging: false,
                searching: false,
                ordering: false,
                info: false,
                ajax: url,
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        className: 'text-center'
                    },
                    {
                        data: 'deskripsi',
                        name: 'deskripsi'
                    },
                    {
                        data: 'jumlah_tagihan',
                        name: 'jumlah_tagihan',
                        className: 'text-end'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center'
                    }
                ],
                drawCallback: function(settings) {
                    $('#total-tagihan').html('<b>Rp. ' + settings.json.total_tagihan_formatted +
                        '</b>');
                }
            });
        });

        $('#modalTagihan').on('hidden.bs.modal', function() {
            $('#formTagihan')[0].reset();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let submitBtn = $('#submitBtnTagihan');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.addClass('d-none');
            btnText.text('Simpan');
            submitBtn.prop('disabled', false);
        });

        $('#formTagihan').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#submitBtnTagihan');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            let id = $('#primary_id').val();
            let url = '{{ route('pembayaran.tambah-tagihan', ['id' => ':id']) }}'.replace(':id',
                id);
            let method = 'POST';

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let formData = new FormData(this);
            formData.append('_method', method);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $('#modalTagihan').modal('hide');
                        audio.play();
                        toastr.success("Tagihan berhasil ditambahkan!", "BERHASIL", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });

                        $('#total_tagihan_all').val('Rp. ' + response.total_tagihan_formatted);
                        $('#sisa_bayar_all').val('Rp. ' + response.sisa_bayar_formatted);

                        $('.table-tagihan').DataTable().ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        audio.play();
                        toastr.error("Ada inputan yang salah!", "GAGAL!", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });

                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, val) {
                            let input = $('#' + key);
                            input.addClass('is-invalid');
                            input.parent().find('.invalid-feedback').remove();
                            input.parent().append(
                                '<span class="invalid-feedback" role="alert"><strong>' +
                                val[0] + '</strong></span>'
                            );
                        });
                    } else {
                        audio.play();
                        toastr.error('Terjadi kesalahan! Coba Beberapa Saat Lagi', "GAGAL!", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });
                    }
                },
                complete: function() {
                    spinner.addClass('d-none');
                    btnText.text('Simpan');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        $(document).on('submit', '.formHargaRumah', function(e) {
            e.preventDefault();

            let form = $(this);
            let id = form.data('id');
            let url = '{{ route('Pembayaran.update-harga-rumah', ['id' => ':id']) }}'.replace(':id', id);
            let formData = new FormData(this);
            formData.append('_method', 'PUT');

            Swal.fire({
                title: 'Yakin mengubah harga kavling?',
                text: "Harga akan disesuaikan dengan harga jual kavling!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<span class="swal-btn-text">Ya, Update</span>',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: false,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary mx-2',
                    cancelButton: 'btn btn-secondary'
                },
                preConfirm: () => {
                    return new Promise((resolve) => {
                        const confirmBtn = Swal.getConfirmButton();
                        const btnText = confirmBtn.querySelector('.swal-btn-text');

                        btnText.innerHTML =
                            `
                            <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> Mengupdate...`;
                        confirmBtn.disabled = true;

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                audio.play();
                                toastr.success("Harga Kavling berhasil diperbarui!",
                                    "BERHASIL", {
                                        progressBar: true,
                                        timeOut: 3500,
                                        positionClass: "toast-bottom-right",
                                    });

                                $('#total_tagihan_all').val(response
                                    .total_tagihan_formatted);
                                $('#sisa_bayar_all').val(response
                                    .sisa_bayar_formatted);
                                $('.table-tagihan').DataTable().ajax.reload(null,
                                    false);

                                Swal.close();
                            },
                            error: function(xhr) {
                                if (xhr.status === 422) {
                                    audio.play();
                                    toastr.error("Ada inputan yang salah!",
                                        "GAGAL!", {
                                            progressBar: true,
                                            timeOut: 3500,
                                            positionClass: "toast-bottom-right",
                                        });

                                    let errors = xhr.responseJSON.errors;
                                    $.each(errors, function(key, val) {
                                        let input = $('#' + key);
                                        input.addClass('is-invalid');
                                        input.parent().find(
                                                '.invalid-feedback')
                                            .remove();
                                        input.parent().append(
                                            '<span class="invalid-feedback" role="alert"><strong>' +
                                            val[0] + '</strong></span>'
                                        );
                                    });

                                    btnText.innerHTML = 'Ya, Update';
                                    confirmBtn.disabled = false;
                                }
                            }
                        });
                    });
                }
            });
        });

        $(document).on('click', '.delete-tagihan', function(e) {
            e.preventDefault();

            const form = $(this).closest('form');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<span class="swal-btn-text">Ya, Hapus</span>',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: false,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger mx-2',
                    cancelButton: 'btn btn-secondary'
                },
                preConfirm: () => {
                    return new Promise((resolve) => {
                        const confirmBtn = Swal.getConfirmButton();
                        const btnText = confirmBtn.querySelector('.swal-btn-text');

                        btnText.innerHTML =
                            `<span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> Menghapus...`;
                        confirmBtn.disabled = true;

                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: form.serialize(),
                            success: function(response) {
                                audio.play();
                                toastr.success("Tagihan telah dihapus!",
                                    "BERHASIL", {
                                        progressBar: true,
                                        timeOut: 3500,
                                        positionClass: "toast-bottom-right"
                                    });

                                $('.table-tagihan').DataTable().ajax.reload(null,
                                    false);
                                $('.table-pemasukan').DataTable().ajax.reload(null,
                                    false);
                                $('#total_tagihan_all').val(response
                                    .total_tagihan_formatted);
                                $('#jumlah_bayar_all').val(response
                                    .jumlah_bayar_formatted);
                                $('#sisa_bayar_all').val(response
                                    .sisa_bayar_formatted);

                                Swal.close();
                            },
                            error: function() {
                                audio.play();
                                toastr.error("Gagal menghapus data.", "GAGAL!", {
                                    progressBar: true,
                                    timeOut: 3500,
                                    positionClass: "toast-bottom-right"
                                });

                                btnText.innerHTML = `Ya, Hapus`;
                                confirmBtn.disabled = false;
                            }
                        });
                    });
                }
            });
        });

        $(function() {
            var customerId = '{{ $customer->id }}';
            var url = "{{ route('pembayaran.detail-pemasukan', ['id' => '__id__']) }}".replace('__id__',
                customerId);

            $('.table-pemasukan').DataTable({
                processing: false,
                serverSide: true,
                paging: false,
                searching: false,
                ordering: false,
                info: false,
                ajax: url,
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        className: 'text-center'
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal'
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan'
                    },
                    {
                        data: 'kategori',
                        name: 'kategori'
                    },
                    {
                        data: 'jumlah',
                        name: 'jumlah',
                        className: 'text-end'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center'
                    }
                ],
                drawCallback: function(settings) {
                    $('#total-pemasukan').html('<b>Rp. ' + settings.json.total_pemasukan_formatted +
                        '</b>');
                }
            });
        });

        $('#modalPemasukan').on('hidden.bs.modal', function() {
            $('#formPemasukan')[0].reset();
            $('.select-kategori-transaksi-pemasukan').val('').trigger('change');
            $('.select-cara-bayar').val('').trigger('change');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let submitBtn = $('#submitBtnPemasukan');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.addClass('d-none');
            btnText.text('Simpan');
            submitBtn.prop('disabled', false);

            $('.label-tagihan').addClass('d-none');
            $('.div-tagihan').addClass('d-none');

            $('#previewLampiran').html(`<span style="color: #6c757d;">Tidak ada berkas</span>`);
        });

        $('#formPemasukan').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#submitBtnPemasukan');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            let id = $('#primary_id').val();
            let url = '{{ route('pembayaran.tambah-pemasukan', ['id' => ':id']) }}'.replace(':id',
                id);
            let method = 'POST';

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let formData = new FormData(this);
            formData.append('_method', method);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $('#modalPemasukan').modal('hide');
                        audio.play();
                        let msg = "Pemasukan berhasil ditambahkan!";
                        toastr.success(msg, "BERHASIL", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });

                        $('#jumlah_bayar_all').val(response.jumlah_bayar);
                        $('#total_tagihan_all').val(response.total_tagihan);
                        $('#sisa_bayar_all').val(response.sisa_bayar);

                        $('.table-pemasukan').DataTable().ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        audio.play();
                        toastr.error("Ada inputan yang salah!", "GAGAL!", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });

                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, val) {
                            let input = $('#' + key);
                            input.addClass('is-invalid');
                            input.parent().find('.invalid-feedback').remove();
                            input.parent().append(
                                '<span class="invalid-feedback" role="alert"><strong>' +
                                val[0] + '</strong></span>'
                            );
                        });
                    } else {
                        audio.play();
                        toastr.error("Gagal menambahkan data.", "GAGAL!", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right"
                        });
                    }
                },
                complete: function() {
                    spinner.addClass('d-none');
                    btnText.text('Simpan');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.delete-pemasukan', function(e) {
            e.preventDefault();

            const form = $(this).closest('form');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<span class="swal-btn-text">Ya, Hapus</span>',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: false,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger mx-2',
                    cancelButton: 'btn btn-secondary'
                },
                preConfirm: () => {
                    return new Promise((resolve) => {
                        const confirmBtn = Swal.getConfirmButton();
                        const btnText = confirmBtn.querySelector('.swal-btn-text');

                        btnText.innerHTML =
                            `<span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> Menghapus...`;
                        confirmBtn.disabled = true;

                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: form.serialize(),
                            success: function(response) {
                                audio.play();
                                toastr.success("Pemasukan telah dihapus!",
                                    "BERHASIL", {
                                        progressBar: true,
                                        timeOut: 3500,
                                        positionClass: "toast-bottom-right"
                                    });

                                $('.table-pemasukan').DataTable().ajax.reload(null,
                                    false);
                                $('#jumlah_bayar_all').val(response.jumlah_bayar);
                                $('#total_tagihan_all').val(response.total_tagihan);
                                $('#sisa_bayar_all').val(response.sisa_bayar);

                                Swal.close();
                            },
                            error: function() {
                                audio.play();
                                toastr.error("Gagal menghapus data.", "GAGAL!", {
                                    progressBar: true,
                                    timeOut: 3500,
                                    positionClass: "toast-bottom-right"
                                });

                                btnText.innerHTML = `Ya, Hapus`;
                                confirmBtn.disabled = false;
                            }
                        });
                    });
                }
            });
        });
    </script>
@endpush
