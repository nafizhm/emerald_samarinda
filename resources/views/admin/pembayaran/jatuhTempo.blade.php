@extends('admin.layout')
@section('page_title', 'Data Jatuh Tempo')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Pembayaran</p><h1>Data Jatuh Tempo</h1><p class="workspace-description">Tinjau tagihan, pembayaran, dan catatan transaksi pelanggan.</p></div>
                        <div class="d-flex align-items-center">
                            <select id="statusFilter" class="select-filter form-select">
                                <option value="">Semua Status</option>
                                <option value="telat">Hanya Telat</option>
                                <option value="lancar">Hanya Lancar</option>
                                <option value="lunas">Hanya Lunas</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="lokasiTabs">
                        @foreach ($lokasi as $index => $item)
                            <li class="nav-item">
                                <a class="nav-link lokasi-tab {{ $index == 0 ? 'active' : '' }}"
                                    data-id="{{ $item->id }}">
                                    {{ $item->nama_kavling }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <table id="data-table" class="table small table-sm table-bordered table-striped data-table">
                        <thead>
                            <tr>
                                <th rowspan="2" width="2%">No</th>
                                <th colspan="4" class="text-center">Pelanggan</th>
                                <th colspan="1" class="text-center">Dana Masuk</th>
                                <th rowspan="2" width="10%">Sisa Pembayaran</th>
                                <th rowspan="2" width="10%">Keterlambatan</th>
                                <th rowspan="2" width="10%" class="text-center">Aksi</th>
                            </tr>
                            <tr>
                                <th width="12%">Jatuh Tempo</th>
                                <th width="12%">Nama Pelanggan</th>
                                <th width="10%">Lokasi Unit</th>
                                <th width="10%">Harga Tanah Kavling</th>

                                <th width="10%">Pembayaran</th>
                            </tr>
                        </thead>
                    </table>
                    <tbody></tbody>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade text-left" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false" data-focus="false">

        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="myModalLabel160">Pembayaran
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <form id="formData">
                    @csrf
                    <input type="hidden" id="primary_id" name="primary_id" value="">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="control-label col-sm-3">Tanggal Pembayaran</label>
                            <div class="col-sm-4">
                                <input name="tanggal_pembayaran" id="tanggal_pembayaran" class="form-control" type="date"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="control-label col-sm-3">Nama Pelanggan</label>
                            <div class="col-sm-6">
                                <input name="nasabah_b" id="nasabah_b" value="" class="form-control" type="text"
                                    disabled>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="control-label col-sm-3">Lokasi Kavling</label>
                            <div class="col-sm-4">
                                <input name="lokasi_b" id="lokasi_b" value="" class="form-control" type="text"
                                    disabled>
                            </div>
                            <div class="col-sm-4">
                                <input name="kode_kavling" id="kode_kavling" value="" class="form-control"
                                    type="text" disabled>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="control-label col-sm-3">Kategori Transaksi</label>
                            <div class="col-sm-4">
                                <select name="id_kategori_transaksi" id="id_kategori_transaksi"
                                    class="form-control select-kategori-transaksi-pemasukan">
                                    <option value=""></option>
                                    @foreach ($kategoriTransaksiPemasukan as $data)
                                        <option value="{{ $data->id }}">{{ $data->kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="control-label col-sm-3">Cara Bayar</label>
                            <div class="col-sm-4">
                                <select name="id_metode" id="id_metode" class="form-control select-cara-bayar">
                                    <option value=""></option>
                                    @foreach ($metodeBayar as $bayar)
                                        <option value="{{ $bayar->id }}">{{ $bayar->jenis_bayar }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="control-label col-sm-3">Nominal</label>
                            <div class="col-sm-4">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input name="nominal_bayar" id="nominal_bayar" class="form-control format-number"
                                        type="text">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="control-label col-sm-3">Keterangan</label>
                            <div class="col-sm-9">
                                <input name="keterangan_pembayaran" id="keterangan_pembayaran" class="form-control"
                                    type="text">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Bukti Pembayaran</label>
                            <div class="col-sm-8">
                                <input type="file" name="file" id="file"
                                    accept=".jpg, .jpeg, .png, .webp, .pdf">
                            </div>
                        </div>
                        <div class="form-group row">
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
                        <button type="submit" class="btn btn-primary ms-1" id="submitBtn">
                            <span class="spinner-border spinner-border-sm mx-1 d-none" role="status"
                                aria-hidden="true"></span>
                            <span class="button-text">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade text-left" id="modalTempo" tabindex="-1" role="dialog" aria-labelledby="modalTempoLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false" data-focus="false">

        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="myModalLabel160">Jatuh Tempo
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <form id="formEdit">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="id_cust" name="id_cust">
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Cicilan Per Bulan</label>
                            <div class="col-sm-9">
                                <input type="text" name="inhouse_perbulan" id="inhouse_perbulan"
                                    class="form-control format-number" min="0">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Tenor Per Bulan</label>
                            <div class="col-sm-9">
                                <input type="number" name="inhouse_tenor" id="inhouse_tenor" min="0"
                                    max="60" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Jatuh Tempo</label>
                            <div class="col-sm-9">
                                <input type="date" name="inhouse_jatuh_tempo" id="inhouse_jatuh_tempo"
                                    class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary ms-1" id="editBtn">
                            <span class="spinner-border spinner-border-sm mx-1 d-none" role="status"
                                aria-hidden="true"></span>
                            <span class="button-text">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

        $(document).ready(function() {
            $('.select-filter').select2({
                width: '150px',
                placeholder: 'Pilih Jenis',
                minimumResultsForSearch: Infinity,
            });

            $('.select-kategori-transaksi-pemasukan').select2({
                width: '100%',
                placeholder: 'Pilih Kategori Transaksi',
            });

            $('.select-cara-bayar').select2({
                width: '100%',
                placeholder: 'Pilih Cara Pembayaran',
            });

            if ($('body').hasClass('dark')) {
                $('.select2-container').addClass('select2-dark');
            }
        });

        let currentLokasi = $('.lokasi-tab.active').data('id');
        let statusFilter = '';

        var table;

        $(function() {
            table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                ajax: {
                    url: "{{ route('pembayaran.jatuhTempo') }}",
                    data: function(d) {
                        d.lokasi_id = currentLokasi;
                        d.filter = statusFilter;
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'tgl_jatuh_tempo',
                        name: 'tgl_jatuh_tempo',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'customer',
                        name: 'customer',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'lokasi',
                        name: 'lokasi',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'hrg_jual',
                        name: 'hrg_jual',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'pembayaran',
                        name: 'pembayaran',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'sisa',
                        name: 'sisa',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'keterlambatan',
                        name: 'keterlambatan',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                columnDefs: [{
                        width: "2%",
                        targets: 0
                    },
                    {
                        width: "12%",
                        targets: 1
                    },
                    {
                        width: "10%",
                        targets: 2
                    },
                    {
                        width: "10%",
                        targets: 3
                    },
                    {
                        width: "10%",
                        targets: 4
                    },
                    {
                        width: "10%",
                        targets: 5
                    },
                    {
                        width: "10%",
                        targets: 6
                    },
                    {
                        width: "10%",
                        targets: 7
                    },
                    {
                        width: "10%",
                        targets: 8
                    },
                ],
                autoWidth: false
            });

            $(document).on('click', '.lokasi-tab', function() {
                $('.lokasi-tab').removeClass('active');
                $(this).addClass('active');

                currentLokasi = $(this).data('id');
                table.ajax.reload();
            });

            $('#statusFilter').on('change', function() {
                statusFilter = $(this).val();
                table.ajax.reload();
            });
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }

        $(document).on('click', '.bayar-button', function() {
            var id = $(this).data('id');
            var url = `{{ route('get.customer', ':id') }}`.replace(':id', id);

            $('#formData')[0].reset();
            $('#previewFoto').html('<span style="color: #6c757d;">Tidak ada File</span>');

            $.get(url, function(response) {
                if (response.success && response.data) {
                    $('#primary_id').val(response.data.customer.id);

                    $('input[name="nasabah_b"]').val(response.data.customer.nama_lengkap);

                    $('input[name="lokasi_b"]').val(response.data.nama_lokasi_gabungan);

                    $('input[name="kode_kavling"]').val(response.data.kode_kavling_gabungan);

                } else {
                    toastr.error('Data customer tidak ditemukan');
                }
            }).fail(function(xhr) {
                console.error('Error:', xhr.responseText);
                toastr.error('Gagal mengambil data customer');
            });
        });

        $(document).on('click', '.edit-button', function() {
            var id = $(this).data('id');
            var url = `{{ route('get.customer', ':id') }}`.replace(':id', id);

            $.get(url, function(response) {
                if (response.success && response.data) {
                    let customer = response.data.customer;

                    $('#id_cust').val(customer.id);
                    $('#inhouse_perbulan').val(Number(customer.inhouse_perbulan || 0).toLocaleString(
                        'id-ID'));
                    $('#inhouse_tenor').val(customer.inhouse_tenor || '');

                    let jatuhTempo = customer.inhouse_jatuh_tempo;
                    if (jatuhTempo) {
                        let formattedDate = jatuhTempo.split(' ')[0];
                        $('#inhouse_jatuh_tempo').val(formattedDate);
                    } else {
                        $('#inhouse_jatuh_tempo').val('');
                    }
                } else {
                    console.error('Data customer tidak ditemukan');
                }
            }).fail(function(xhr) {
                console.error('Error:', xhr.responseText);
            });
        });

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            $('#jenis_pembayaran').val('').trigger('change');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let submitBtn = $('#submitBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.addClass('d-none');
            btnText.text('Simpan');
            submitBtn.prop('disabled', false);
        });

        $('#modalTempo').on('hidden.bs.modal', function() {
            $('#formTempo')[0].reset()

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let submitBtn = $('#editBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.addClass('d-none');
            btnText.text('Simpan');
            submitBtn.prop('disabled', false);
        });

        $('#formData').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#submitBtn');
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
                    $('#modalForm').modal('hide');
                    audio.play();
                    let msg = "Pemasukan berhasil ditambahkan!";
                    toastr.success(msg, "BERHASIL", {
                        progressBar: true,
                        timeOut: 3500,
                        positionClass: "toast-bottom-right",
                    });

                    table.ajax.reload(null, false);
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
                        toastr.error(
                            "Terjadi kesalahan saat menyimpan data",
                            "Gagal!", {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 3000,
                                positionClass: 'toast-bottom-right'
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

        $('#formEdit').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#editBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            let id = $('#id_cust').val();
            let url = '{{ route('pembayaran.editTempo', ['id' => ':id']) }}'.replace(':id',
                id);
            let method = 'PUT';

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let formData = new FormData(this);
            formData.append('_method', method);

            $.ajax({
                url: url,
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#modalTempo').modal('hide');
                    audio.play();
                    let msg = "Jatuh tempo berhasil diupdate!";
                    toastr.success(msg, "BERHASIL", {
                        progressBar: true,
                        timeOut: 3500,
                        positionClass: "toast-bottom-right",
                    });

                    table.ajax.reload(null, false);
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
                        toastr.error(
                            "Terjadi kesalahan saat menyimpan data",
                            "Gagal!", {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 3000,
                                positionClass: 'toast-bottom-right'
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
    </script>
@endpush
