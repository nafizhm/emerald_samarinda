@extends('admin.layout')
@section('page_title', 'Data Customer')
@section('search_placeholder', 'Cari nama customer, kavling...')
@push('styles')
<link rel="stylesheet" href="{{ asset('template/assets/css/customer-custom.css') }}?v={{ filemtime(public_path('template/assets/css/customer-custom.css')) }}">
@endpush
@section('content')
    <div class="page-content dashboard-page workspace-page customer-page customer-list-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading">
                            <p class="dashboard-eyebrow">Customer &amp; penjualan</p>
                            <h1>Customer</h1>
                            <p class="workspace-description">Kelola customer, informasi kavling, dan tahapan penjualan.</p>
                        </div>
                        <div class="customer-list-actions">
                            @if (isset($permissions['tambah']) && $permissions['tambah'] == 1)
                                <a href="{{ route('customer.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg"></i> Tambah customer
                                </a>
                            @endif
                            <button type="button" class="btn btn-light btn-rounded btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalPrint">
                                <i class="bi bi-printer"></i> Cetak daftar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body customer-list-content">
                    <table class="table data-table customer-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Transaksi</th>
                                <th>Customer</th>
                                <th>Marketing</th>
                                <th>Kavling / proyek</th>
                                <th>Tahapan penjualan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade text-left" id="modalPrint" tabindex="-1" role="dialog" aria-labelledby="modalPrintLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-focus="false">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content ">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title white" id="modalPrintLabel">Cetak Transaksi</h5>
                    <button type="button" class="close white" data-bs-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <form id="formPrint" action="{{ route('customer.cetak') }}" method="GET" target="_blank">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="primary_id" name="primary_id">

                        <div class="container-fluid">
                            <div class="row mb-3">
                                <label for="lokasi" class="col-sm-3 col-form-label">Lokasi</label>
                                <div class="col-sm-6">
                                    <select name="lokasi" class="form-control select-lokasi" id="lokasi">
                                        <option value=""></option>
                                        @foreach ($lokasi as $l)
                                            <option value="{{ $l->id }}">{{ $l->nama_kavling }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <span class="button-text">Batal</span>
                        </button>
                        <button type="submit" class="btn btn-primary ms-1" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                                aria-hidden="true"></span>
                            <span class="button-text">Cetak PDF</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade text-left" id="modalCancel" tabindex="-1" role="dialog" aria-labelledby="modalCancelLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-focus="false">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content ">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title white" id="modalCancelLabel">Batalkan transaksi customer</h5>
                    <button type="button" class="close white" data-bs-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <form id="formCancel" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body"><p class="text-muted small">Isi tanggal dan alasan pembatalan transaksi customer ini.</p>
                        <div class="container-fluid">
                            <div class="row mb-3">
                                <label for="tgl_cancel" class="col-sm-4 col-form-label">Tanggal pembatalan <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="date" name="tgl_cancel" id="tgl_cancel" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="alasan_cancel" class="col-sm-4 col-form-label">Alasan pembatalan <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <textarea name="alasan_cancel" id="alasan_cancel" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <span class="button-text">Batal</span>
                        </button>
                        <button type="submit" class="btn btn-danger ms-1" id="btnSubmitCancel">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                                aria-hidden="true"></span>
                            <span class="button-text">Batalkan transaksi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        var permissions = @json($permissions);
        var showActionColumn = true;

        $(document).ready(function() {
            $('.select-lokasi').select2({
                dropdownParent: $('#modalPrint'),
                width: '100%',
                placeholder: 'Semua lokasi',
                minimumResultsForSearch: Infinity,
            });

            if ($('body').hasClass('dark')) {
                $('.select2-container').addClass('select2-dark');
            }
        });

        $(document).ready(function() {
            const successMsg = localStorage.getItem('customerSuccess');
            if (successMsg) {
                if (typeof audio !== 'undefined') audio.play().catch(() => {});
                toastr.success(successMsg, "BERHASIL", {
                    progressBar: true,
                    timeOut: 3500,
                    positionClass: "toast-bottom-right",
                });
                localStorage.removeItem('customerSuccess');
            }
        });

        $(function() {
            var table = $('.data-table').DataTable({
                processing: true,
                language: {search: 'Cari customer:', searchPlaceholder: 'Nama, kode, atau telepon', lengthMenu: 'Tampilkan _MENU_ customer', info: '_START_ - _END_ dari _TOTAL_ customer', infoEmpty: 'Belum ada customer', infoFiltered: '(dari _MAX_ customer)', zeroRecords: 'Customer tidak ditemukan', emptyTable: 'Belum ada transaksi pembelian. Klik Transaksi pembelian baru untuk memulai.', processing: 'Memuat customer...', paginate: {previous: 'Sebelumnya', next: 'Berikutnya'}},
                serverSide: true,
                ordering: false,
                responsive: false,
                autoWidth: false,
                createdRow: function(row) { const labels = ['No', 'Transaksi', 'Customer', 'Marketing', 'Kavling / proyek', 'Tahapan penjualan', 'Aksi']; row.querySelectorAll('td').forEach((cell, index) => cell.setAttribute('data-label', labels[index])); },
                ajax: "{{ route('customer.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'tgl_terima',
                        name: 'tgl_terima',
                        searchable: false
                    },
                    {
                        data: 'nama_lengkap',
                        name: 'nama_lengkap',
                        searchable: true
                    },
                    {
                        data: 'id_marketing',
                        name: 'id_marketing',
                        searchable: false
                    },
                    {
                        data: 'id_lokasi',
                        name: 'id_lokasi',
                        searchable: false
                    },
                    {
                        data: 'id_status_progres',
                        name: 'id_status_progres',
                        className: 'text-center',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: showActionColumn
                    }
                ],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }]
            });
        });

        $(document).on('click', '.cancel-button', function(e) {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            $('#modalCancelLabel').text('Batalkan transaksi ' + nama);
            $('#formCancel').attr('action', '{{ route("customer.destroy", ":id") }}'.replace(':id', id));
            $('#modalCancel').modal('show');
        });

        $('#formCancel').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = $('#btnSubmitCancel');
            const btnText = submitBtn.find('.button-text');
            const spinner = submitBtn.find('.spinner-border');

            spinner.removeClass('d-none');
            btnText.text('Memproses...');
            submitBtn.prop('disabled', true);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(res) {
                    if (res.success) {
                        $('#modalCancel').modal('hide');
                        form[0].reset();
                        if (typeof audio !== 'undefined') audio.play().catch(() => {});
                        toastr.success(res.message, "BERHASIL", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right"
                        });
                        $('.data-table').DataTable().ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON;
                    if (errors && errors.errors) {
                        $.each(errors.errors, function(key, val) {
                            toastr.error(val[0], "VALIDASI", {
                                progressBar: true,
                                timeOut: 3500,
                                positionClass: "toast-bottom-right"
                            });
                        });
                    } else if (errors && errors.message) {
                        toastr.error(errors.message, "GAGAL", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right"
                        });
                    } else {
                        toastr.error("Terjadi kesalahan", "GAGAL", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right"
                        });
                    }
                },
                complete: function() {
                    spinner.addClass('d-none');
                    btnText.text('Batalkan transaksi');
                    submitBtn.prop('disabled', false);
                }
            });
        });
    </script>
@endpush
