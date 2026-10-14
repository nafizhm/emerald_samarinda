@extends('admin.layout')
@section('page_title', 'Data Legalitas')
@section('content')
    <style>
        td.keterangan-col {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #fotoContainer img {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        #fotoContainer img:hover {
            transform: scale(1.05);
        }
    </style>

    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Dokumen & legalitas</p><h1>Data Legalitas</h1><p class="workspace-description">Kelola dokumen legalitas dan lampiran terkait.</p></div>
                        <div class="d-flex align-items-center gap-2 w-25">
                            <select name="filter" id="filter" class="form-control form-control-sm select-filter"
                                style="height: 32px;">
                                <option value="all">Semua</option>
                                <option value="laku">Sudah Laku</option>
                            </select>
                            <a href="javascript:void(0);" onclick="reloadTable()"
                                class="btn btn-light btn-rounded btn-sm d-flex align-items-center" title="Perbarui daftar"
                                style="height: 32px;">
                                <i class="bi bi-arrow-clockwise me-1"></i> Perbarui
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="4%">No</th>
                                    <th width="10%">Kode Kavling</th>
                                    <th width="18%">Nama Konsumen</th>
                                    <th width="15%">Atas Nama Surat</th>
                                    <th width="12%">No. Surat</th>
                                    <th width="10%">Progres</th>
                                    <th width="8%">Bukti Foto</th>
                                    <th width="13%">Keterangan</th>
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

    <div class="modal fade text-left" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false" data-focus="false">

        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="myModalLabel160">Legalitas
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
                            <label for="atas_nama" class="col-sm-4 col-form-label">Atas Nama</label>
                            <div class="col-sm-8">
                                <input type="text" id="atas_nama" class="form-control" name="atas_nama"
                                    placeholder="Masukkan nama pemilik surat">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="no_surat" class="col-sm-4 col-form-label">No. Surat</label>
                            <div class="col-sm-8">
                                <input type="text" id="no_surat" class="form-control" name="no_surat"
                                    placeholder="Masukkan nomor surat">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="keterangan" class="col-sm-4 col-form-label">Keterangan</label>
                            <div class="col-sm-8">
                                <textarea id="keterangan_legalitas" class="form-control" name="keterangan_legalitas" rows="3"
                                    placeholder="Tambahkan keterangan jika ada"></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="keterangan" class="col-sm-4 col-form-label">Upload Foto</label>
                            <div class="col-sm-8">
                                <input type="file" multiple id="upload_foto" class="form-control" name="upload_foto[]"
                                    accept="image/*">
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

    <!-- Modal Foto -->
    <div class="modal fade" id="modalFoto" tabindex="-1" aria-labelledby="modalFotoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content workspace-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFotoLabel">Foto Legalitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="fotoContainer" class="d-flex flex-wrap gap-3 justify-content-start">
                        <!-- Foto akan dimuat di sini -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select-filter').select2({
                width: '100%',
                placeholder: 'Pilih Status',
                minimumResultsForSearch: Infinity,
            });

            if ($('body').hasClass('dark')) {
                $('.select2-container').addClass('select2-dark');
            }
        });

        var permissions = @json($permissions);
        var showActionColumn = (permissions['edit'] == 1 || permissions['hapus'] == 1);
        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

        var table;

        $(function() {
            table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                ajax: {
                    url: "{{ route('legalitas.index') }}",
                    data: function(d) {
                        d.filter = $('.select-filter').val();
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
                        data: 'kode_kavling',
                        name: 'kode_kavling',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'nama_konsumen',
                        name: 'nama_konsumen',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'atas_nama',
                        name: 'atas_nama',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'no_surat',
                        name: 'no_surat',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'progres',
                        name: 'progres',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'bukti_foto',
                        name: 'bukti_foto',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan',
                        orderable: false,
                        searchable: false,
                        className: 'keterangan-col'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: showActionColumn,
                        className: "text-center"
                    }
                ],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }]
            });

            $('.select-filter').on('change', function() {
                table.ajax.reload();
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
                    $('#atas_nama').val(response.data.atas_nama);
                    $('#no_surat').val(response.data.no_surat);
                    $('#keterangan_legalitas').val(response.data.keterangan_legalitas);

                    $('#modalForm').modal('show');
                }
            });
        });

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            $('#primary_id').val('');
            $('#atas_nama').val('');
            $('#no_surat').val('');
            $('#keterangan_legalitas').val('');

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
            let url = '{{ route('legalitas.update', ['legalita' => ':id']) }}'.replace(':id', id);
            let method = 'PUT';

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
                        let msg = "Data berhasil diupdate!";
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

        $(document).on('click', '.lihat-button', function() {
            let url = $(this).data('url');
            let container = $('#fotoContainer');
            container.html('<p class="text-muted">Loading...</p>');

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    container.empty();

                    if (response.success && response.data.length > 0) {
                        response.data.forEach(item => {
                            let isPdf = item.url.toLowerCase().endsWith('.pdf');
                            let content = isPdf ?
                                `<a href="${item.url}" target="_blank" class="btn btn-outline-danger btn-sm">Lihat PDF</a>` :
                                `
                            <a href="${item.url}" target="_blank">
                                <img src="${item.url}" 
                                     class="img-thumbnail" 
                                     style="width: 180px; height: auto; cursor: pointer;"
                                     title="Klik untuk lihat penuh">
                            </a>
                        `;

                            container.append(`
                        <div class="text-center">
                            ${content}
                            <div class="mt-1 text-sm fw-bold">${item.nama_file}</div>
                            <div class="text-muted" style="font-size: 0.8rem;">${item.tanggal}</div>
                        </div>
                    `);
                        });
                    } else {
                        container.html(
                            '<p class="text-muted">Tidak ada foto legalitas untuk customer ini.</p>'
                        );
                    }
                },
                error: function(xhr) {
                    container.html('<p class="text-danger">Gagal memuat data.</p>');
                    console.error(xhr.responseText);
                }
            });
        });

        $('#modalFoto').on('hidden.bs.modal', function() {
            $('#fotoContainer').empty();
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
