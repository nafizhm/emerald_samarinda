@extends('admin.layout')
@section('page_title', 'Tim Marketing')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Tim penjualan</p><h1>Tim Marketing</h1><p class="workspace-description">Kelola anggota marketing dan informasi kontak.</p></div>
                        <div class="d-flex align-items-center">
                            @if (isset($permissions['tambah']) && $permissions['tambah'] == 1)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalForm">
                                    <i class="bi bi-plus-lg"></i> Tambah Marketing
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
                                    <th>Kode Marketing</th>
                                    <th>Nama Marketing</th>
                                    <th>Alamat</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="modalFormLabel">Marketing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Tutup"></button>
                </div>
                <form id="formData">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="primary_id" name="primary_id">

                        <div class="container-fluid">
                            <div class="row mb-3">
                                <label for="nama_marketing" class="col-sm-3 col-form-label">Nama Marketing</label>
                                <div class="col-sm-6">
                                    <input type="text" name="nama_marketing" id="nama_marketing" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="alamat" class="col-sm-3 col-form-label">Alamat</label>
                                <div class="col-sm-6">
                                    <input type="text" name="alamat" id="alamat" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="jenis_kelamin" class="col-sm-3 col-form-label">Jenis Kelamin</label>
                                <div class="col-sm-3">
                                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-control select-jk">
                                        <option value=""></option>
                                        <option value="Laki-laki">Laki-Laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="pekerjaan" class="col-sm-3 col-form-label">Pekerjaan</label>
                                <div class="col-sm-6">
                                    <input type="text" name="pekerjaan" id="pekerjaan" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="no_telp" class="col-sm-3 col-form-label">No. Telp</label>
                                <div class="col-sm-6">
                                    <input type="number" name="no_telp" id="no_telp" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="foto" class="col-sm-3 col-form-label">Foto</label>
                                <div class="col-sm-6">
                                    <input type="file" name="foto" id="foto">
                                    <small id="error-foto" class="error-text text-danger"></small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="status" class="col-sm-3 col-form-label">Status</label>
                                <div class="col-sm-3">
                                    <select name="status" id="status" class="form-control select-status">
                                        <option value=""></option>
                                        <option value="1">Aktif</option>
                                        <option value="0">Tidak Aktif</option>
                                    </select>
                                </div>
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
            $('.select-jk').select2({
                dropdownParent: $('#modalForm'),
                width: '100%',
                placeholder: 'Pilih Jenis Kelamin',
                allowClear: true,
                minimumResultsForSearch: Infinity,
            });

            if ($('body').hasClass('dark')) {
                $('.select2-container').addClass('select2-dark');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modalForm');

            modal.addEventListener('shown.bs.modal', function() {
                console.log('Modal opened - scroll should work now');

                const modalBody = modal.querySelector('.modal-body');
                modalBody.addEventListener('scroll', function() {
                    console.log('Modal body is scrolling:', this.scrollTop);
                });
            });
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
                ajax: "{{ route('marketing.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'kode_marketing_render',
                        name: 'kode_marketing_render',
                    },
                    {
                        data: 'nama_marketing',
                        name: 'nama_marketing',
                    },
                    {
                        data: 'alamat_render',
                        name: 'alamat_render',
                    },
                    {
                        data: 'status_render',
                        name: 'status_render',
                        className: 'text-center'
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
            $('.data-table').DataTable().ajax.reload(null, false);
        }

        // Tombol edit
        $(document).on('click', '.edit-button', function() {
            var url = $(this).data('url');
            $.get(url, function(response) {
                if (response.success) {
                    $('#primary_id').val(response.data.id);

                    $('#nama_marketing').val(response.data.nama_marketing);
                    $('#alamat').val(response.data.alamat);
                    $('#jenis_kelamin').val(response.data.jenis_kelamin).trigger('change');
                    $('#pekerjaan').val(response.data.pekerjaan);
                    $('#no_telp').val(response.data.no_telp);
                    $('#foto').val('');
                    $('#status').val(response.data.status).trigger('change');
                    $('#atas_nama').val(response.data.atas_nama);

                    $('#modalForm').modal('show');
                }
            });
        });


        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            $('#primary_id').val('');
            $('#nama_marketing').val('');
            $('#alamat').val('');
            $('#jenis_kelamin').val('').trigger('change');
            $('#pekerjaan').val('');
            $('#no_telp').val('');
            $('#foto').val('');
            $('#status').val('').trigger('change');
            $('#atas_nama').val('');

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
            let url = id ? '{{ route('marketing.update', ['marketing' => ':id']) }}'.replace(':id',
                    id) :
                '{{ route('marketing.store') }}';
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
                                toastr.error("Gagal menghapus Pengguna.",
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
