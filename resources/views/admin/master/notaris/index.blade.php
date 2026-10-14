@extends('admin.layout')
@section('page_title', 'Data Notaris')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Data referensi</p><h1>Data Notaris</h1><p class="workspace-description">Kelola daftar notaris dan informasi kontaknya.</p></div>
                        <div class="d-flex align-items-center">
                            @if (isset($permissions['tambah']) && $permissions['tambah'] == 1)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalForm">
                                    <i class="bi bi-plus-lg"></i> Tambah Notaris
                                </button>
                            @endif
                            <a href="javascript:void(0);" onclick="reloadTable()"
                                class="btn btn-light btn-rounded btn-sm ms-2" title="Perbarui daftar">
                                <i class="bi bi-arrow-clockwise"></i> Perbarui
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Notaris</th>
                                    <th>Alamat</th>
                                    <th>No Telp</th>
                                    <th>Keterangan</th>
                                    <th class="text-center" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade text-left" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false" data-focus="false">

        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="myModalLabel160">Pengguna
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <form id="formData">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="primary_id" name="primary_id">

                        <div class="row mb-3">
                            <label for="nama_notaris" class="col-sm-4 col-form-label">Nama Notaris</label>
                            <div class="col-sm-8">
                                <input type="text" id="nama_notaris" class="form-control" name="nama_notaris">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="alamat_notaris" class="col-sm-4 col-form-label">Alamat</label>
                            <div class="col-sm-8">
                                <input type="text" id="alamat_notaris" class="form-control" name="alamat_notaris">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="telp_notaris" class="col-sm-4 col-form-label">No Telp</label>
                            <div class="col-sm-8">
                                <input type="text" id="telp_notaris" class="form-control" name="telp_notaris">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="keterangan_notaris" class="col-sm-4 col-form-label">Keterangan</label>
                            <div class="col-sm-8">
                                <input type="text" id="keterangan_notaris" class="form-control"
                                    name="keterangan_notaris">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <span class="button-text">Batal</span>
                        </button>
                        <button type="submit" class="btn btn-primary ms-1" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
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
        $(document).ready(function() {
            $('.select-status').select2({
                dropdownParent: $('#modalForm'),
                width: '100%',
                placeholder: 'Pilih Status',
                minimumResultsForSearch: Infinity,
            });
            $('.select-role').select2({
                dropdownParent: $('#modalForm'),
                width: '100%',
                placeholder: 'Pilih Role',
                allowClear: true,
                minimumResultsForSearch: Infinity,
            });

            if ($('body').hasClass('dark')) {
                $('.select2-container').addClass('select2-dark');
            }
        });

        var permissions = @json($permissions);
        var showActionColumn = (permissions['edit'] == 1 || permissions['hapus'] == 1);
        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

        $(function() {
            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                ajax: "{{ route('notaris.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'nama_notaris',
                        name: 'nama_notaris',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'alamat_notaris',
                        name: 'alamat_notaris',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'telp_notaris',
                        name: 'telp_notaris',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'keterangan_notaris',
                        name: 'keterangan_notaris',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: showActionColumn,
                        className: 'text-center'
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

        function reloadTable() {
            table.ajax.reload(null, false);
        }

        // Tombol edit
        $(document).on('click', '.edit-button', function() {
            var url = $(this).data('url');
            $.get(url, function(response) {
                if (response.success) {
                    $('#primary_id').val(response.data.id);
                    $('#nama_notaris').val(response.data.nama_notaris);
                    $('#alamat_notaris').val(response.data.alamat_notaris);
                    $('#telp_notaris').val(response.data.telp_notaris);
                    $('#keterangan_notaris').val(response.data.keterangan_notaris);

                    $('#modalForm').modal('show');
                }
            });
        });

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            $('#primary_id').val('');
            $('#nama_notaris').val('');
            $('#alamat_notaris').val('');
            $('#telp_notaris').val('');
            $('#keterangan_notaris').val('');

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            let submitBtn = $('#submitBtn');
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
            let url = id ? '{{ route('notaris.update', ['notari' => ':id']) }}'.replace(':id',
                    id) :
                '{{ route('notaris.store') }}';
            let method = id ? 'PUT' : 'POST';

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
                success: function(res) {
                    if (res.success) {
                        $('#modalForm').modal('hide');
                        audio.play();
                        let msg = id ? "Data berhasil diupdate!" : "Data berhasil ditambahkan!";
                        toastr.success(msg, "BERHASIL", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });
                        $('.data-table').DataTable().ajax.reload();
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
                        let msg = xhr.responseJSON?.message || "Terjadi kesalahan server!";
                        toastr.error(msg, "ERROR!", {
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

        // Hapus data
        $(document).on('click', '.delete-button', function(e) {
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
                            success: function(res) {
                                if (res.success) {
                                    audio.play();
                                    toastr.success("Data berhasil dihapus!",
                                        "BERHASIL", {
                                            progressBar: true,
                                            timeOut: 3500,
                                            positionClass: "toast-bottom-right"
                                        });

                                    $('.data-table').DataTable().ajax.reload(null,
                                        false);
                                    Swal.close();
                                }
                            },
                            error: function() {
                                audio.play();
                                toastr.error("Gagal menghapus Data.",
                                    "GAGAL!", {
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
