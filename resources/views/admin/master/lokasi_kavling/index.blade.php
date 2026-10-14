@extends('admin.layout')
@section('page_title', 'Data Lokasi Kavling')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Proyek & kavling</p><h1>Data Lokasi Kavling</h1><p class="workspace-description">Kelola lokasi proyek, denah, dan informasi kavling.</p></div>
                        <div class="d-flex align-items-center">
                            @if (isset($permissions['tambah']) && $permissions['tambah'] == 1)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalForm">
                                    <i class="bi bi-plus-lg"></i> Tambah Lokasi Kavling
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

                    <table class="table data-table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="50px">No</th>
                                <th>Nama Kavling</th>
                                <th>Alamat Kavling</th>
                                <th class="text-center">Jumlah</th>
                                <th>Detail</th>
                                <th width="100px" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="modalFormLabel">Lokasi Kavling</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Tutup"></button>
                </div>
                <form id="formData">
                    @csrf
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <input type="hidden" id="primary_id" name="primary_id">

                        <div class="container-fluid">
                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Nama Lokasi Kavling</label>
                                <div class="col-sm-8">
                                    <input type="text" name="nama_kavling" id="nama_kavling" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Nama Singkat</label>
                                <div class="col-sm-8">
                                    <input type="text" name="nama_singkat" id="nama_singkat" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Header</label>
                                <div class="col-sm-8">
                                    <input type="text" name="header" id="header" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Alamat Kavling</label>
                                <div class="col-sm-8">
                                    <input type="text" name="alamat" id="alamat" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Nama Perusahaan</label>
                                <div class="col-sm-8">
                                    <input type="text" name="nama_perusahaan" id="nama_perusahaan" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Nama Admin</label>
                                <div class="col-sm-8">
                                    <input type="text" name="nama_admin" id="nama_admin" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Nama Mengetahui</label>
                                <div class="col-sm-8">
                                    <input type="text" name="nama_mengetahui" id="nama_mengetahui" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Alamat Perusahaan</label>
                                <div class="col-sm-8">
                                    <input type="text" name="alamat_perusahaan" id="alamat_perusahaan"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Telp Perusahaan</label>
                                <div class="col-sm-8">
                                    <input type="telp" name="telp_perusahaan" id="telp_perusahaan"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Kota Penandatangan</label>
                                <div class="col-sm-8">
                                    <input type="text" name="kota_penandatangan" id="kota_penandatangan"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Nama Penandatangan</label>
                                <div class="col-sm-8">
                                    <input type="text" name="nama_penandatangan" id="nama_penandatangan"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Jabatan Penandatangan</label>
                                <div class="col-sm-8">
                                    <input type="text" name="jabatan_penandatangan" id="jabatan_penandatangan"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Kop Surat</label>
                                <div class="col-sm-8">
                                    <input type="file" name="kop_surat" id="kop_surat">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Kwitansi</label>
                                <div class="col-sm-8">
                                    <input type="file" name="bg_kwitansi" id="bg_kwitansi">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="stt_tampil" class="col-sm-4 col-form-label">Jenis Pembelian</label>
                                <div class="col-sm-3">
                                    <select name="stt_tampil" id="stt_tampil" class="form-control select-tampil">
                                        <option value=""></option>
                                        <option value="1">Penjualan
                                        </option>
                                        <option value="2">Proyek
                                        </option>
                                        <option value="3">Keduanya
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Urutan Lokasi</label>
                                <div class="col-sm-2">
                                    <input type="number" name="urutan" id="urutan" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Foto Kavling</label>
                                <div class="col-sm-8">

                                    <div class="mb-2">
                                        <img id="preview_foto_kavling"
                                            src="{{ isset($data->foto_kavling) ? asset('assets/homepage/' . $data->foto_kavling) : '' }}"
                                            alt="Preview Foto Kavling" class="img-thumbnail"
                                            style="max-height: 150px; {{ isset($data->foto_kavling) ? '' : 'display:none;' }}">
                                    </div>

                                    <input type="file" name="foto_kavling" id="foto_kavling"class="form-control"
                                        accept="image/*">
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
        document.getElementById('foto_kavling').addEventListener('change', function(e) {
            const preview = document.getElementById('preview_foto_kavling');
            const file = e.target.files[0];

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });

        $(document).ready(function() {

            $('.stt_tampil').select2({
                dropdownParent: $('#modalForm'),
                width: '100%',
                placeholder: 'Pilih Status',
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
                ajax: "{{ route('lokasi-kavling.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'nama_kavling',
                        name: 'nama_kavling',
                        render: function(data, type, row) {
                            return `
                                <strong>${data}</strong><br>
                                <span>${row.nama_perusahaan}</span>
                            `;
                        }
                    },
                    {
                        data: 'alamat',
                        name: 'alamat'
                    },
                    {
                        data: 'jumlah_kavling',
                        name: 'jumlah_kavling',
                        className: 'text-center'
                    },
                    {
                        data: 'detail',
                        name: 'detail',
                        orderable: false,
                        searchable: false,
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

                    $('#nama_kavling').val(response.data.nama_kavling);
                    $('#nama_singkat').val(response.data.nama_singkat);
                    $('#header').val(response.data.header);
                    $('#alamat').val(response.data.alamat);
                    $('#nama_perusahaan').val(response.data.nama_perusahaan);
                    $('#nama_admin').val(response.data.nama_admin);
                    $('#nama_mengetahui').val(response.data.nama_mengetahui);
                    $('#alamat_perusahaan').val(response.data.alamat_perusahaan);
                    $('#telp_perusahaan').val(response.data.telp_perusahaan);
                    $('#kota_penandatangan').val(response.data.kota_penandatangan);
                    $('#nama_penandatangan').val(response.data.nama_penandatangan);
                    $('#jabatan_penandatangan').val(response.data.jabatan_penandatangan);
                    $('#stt_tampil').val(response.data.stt_tampil);
                    $('#urutan').val(response.data.urutan);
                    if (response.data.foto_kavling) {
                        $('#preview_foto_kavling')
                            .attr('src', '{{ asset('assets/homepage') }}/' + response.data.foto_kavling)
                            .show();
                    } else {
                        $('#preview_foto_kavling').hide();
                    }

                    $('#modalForm').modal('show');
                }
            });
        });

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            $('#primary_id').val('');

            $('#preview_foto_kavling').hide().attr('src', '');
            $('#foto_kavling').val('');

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
            let url = id ? '{{ route('lokasi-kavling.update', ['lokasi_kavling' => ':id']) }}'.replace(':id',
                    id) :
                '{{ route('lokasi-kavling.store') }}';
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
                        let msg = id ? "Pengguna berhasil diupdate!" : "Pengguna berhasil ditambahkan!";
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
                html: "Lokasi kosong akan dihapus permanen.<br><br><label><input type=\"checkbox\" id=\"confirm-location-delete\"> Saya memahami dan menyetujui penghapusan ini.</label>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<span class="swal-btn-text">Ya, Hapus</span>',
                cancelButtonText: 'Batal',
                didOpen: () => { const confirmButton = Swal.getConfirmButton(); confirmButton.disabled = true; document.getElementById("confirm-location-delete").addEventListener("change", function() { confirmButton.disabled = !this.checked; }); },
                showLoaderOnConfirm: false,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger mx-2',
                    cancelButton: 'btn btn-secondary'
                },
                preConfirm: () => {
                    if (!document.getElementById('confirm-location-delete')?.checked) { Swal.showValidationMessage('Centang persetujuan terlebih dahulu.'); return false; }
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
                                toastr.error("Gagal menghapus data.",
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
