@extends('admin.layout')
@section('page_title', 'Dokumen Pelanggan')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Dokumen pelanggan</p><h1>Dokumen Pelanggan</h1><p class="workspace-description">Kelola berkas dan lampiran pelanggan.</p></div>
                    </div>
                </div>
                <div class="card-body">
                    <input type="hidden" name="id" value="{{ $data ? $data->id : '' }}">

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label">Nama Pelanggan</label>
                        <div class="col-sm-3">
                            <select class="form-control select-nasabah" id="nama_nasabah" name="nama_nasabah">
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label">No. KTP</label>
                        <div class="col-sm-3">
                            <input type="number" name="no_ktp" class="form-control" disabled>
                        </div>
                        <label class="col-sm-2 col-form-label">No. Telp</label>
                        <div class="col-sm-3">
                            <input type="number" name="no_ktp_p" class="form-control" disabled>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="lokasi_perumahan" class="col-sm-3 col-form-label">Lokasi Kavling</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="lokasi_perumahan" disabled>
                        </div>
                        <label class="col-sm-2 col-form-label">Lokasi Kav/Blok</label>
                        <div class="col-sm-3">
                            <input type="text" name="kode_kavling" class="form-control" disabled>
                        </div>
                    </div>
                    @if ($permissions['tambah'] == 1)
                        <div class="modal-footer">
                            <button type="button" class="btn btn-info mb-3" id="modal-button" data-bs-toggle="modal"
                                data-bs-target="#modalForm">
                                <i class="bi bi-upload"></i> Upload File
                            </button>
                        </div>
                    @endif

                    <table id="data-table" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama File</th>
                                <th>File Lampiran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
        </section>
    </div>

    <div class="modal fade text-left" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-bs-focus="false">

        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="myModalLabel160">Upload File Pelanggan</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <form id="formData">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="primary_id" name="primary_id">

                        <div class="form-group">
                            <label for="tanggal">Tanggal Upload</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal"
                                value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label for="nama_file">Nama File</label>
                            <select name="nama_file" id="nama_file" class="form-control nama-file">
                                <option value="">-- Pilih Nama File --</option>
                                <option value="KTP">Foto KTP</option>
                                <option value="KK">Foto Kartu Keluarga</option>
                                <option value="Pemohon">Foto Pemohon</option>
                                <option value="KTP Pasangan">Foto KTP Pasangan</option>
                                <option value="NPWP">Foto NPWP</option>
                                <option value="BPJS">Foto BPJS</option>
                                <option value="Bukti Bayar">Bukti Bayar</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="lampiran">File Lampiran</label>
                            <input type="file" is="lampiran" name="lampiran">
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea class="form-control" name="keterangan"></textarea>
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
        var permissions = @json($permissions);
        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

        $(document).ready(function() {
            $('.select-nasabah').select2({
                width: '100%',
                placeholder: 'Pilih Pelanggan',
                minimumResultsForSearch: 1,
                ajax: {
                    url: '{{ route('nasabah.search') }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(response) {
                        return {
                            results: response.data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.kode_customer + '-' + item.nama_lengkap
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });

            $('.select-nasabah').on('change', function() {
                var nasabahId = $(this).val();

                if (nasabahId) {
                    $.ajax({
                        url: `{{ route('nasabah.details', ':id') }}`.replace(':id', nasabahId),
                        method: 'GET',
                        success: function(response) {
                            let data = response.data;
                            $('input[name="no_ktp"]').val(data.no_ktp);
                            $('input[name="no_ktp_p"]').val(data.no_telp);
                            $('input[name="lokasi_perumahan"]').val(data.lokasi_perumahan);
                            $('input[name="kode_kavling"]').val(data.lokasi_kav_blok);
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Data nasabah tidak ditemukan!',
                            });
                        }
                    });
                }
            });
        });

        $('.select-nasabah').on('change', function() {
            nasabahId = $(this).val();
            $('#primary_id').val(nasabahId);
            loadTableFiles(nasabahId);
        });

        function loadTableFiles(nasabahId) {
            $('#data-table tbody').empty();

            $.ajax({
                url: `{{ route('getFileNasabah', ':id') }}`.replace(':id', nasabahId),
                method: 'GET',
                success: function(data) {
                    if (data.data.length === 0) {
                        $('#data-table tbody').html(
                            '<tr><td colspan="4" class="text-center">Belum ada file.</td></tr>'
                        );
                    } else {
                        data.data.forEach(function(file, index) {
                            console.log(file);
                            $('#data-table tbody').append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${file.nama_file}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewFile('${file.folder}', '${file.lampiran}', '${file.nama_file}')">View</button>
                                        <a href="/berkas_user/${file.lampiran}" class="btn btn-sm btn-success" download>Download</a>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="deleteFile(${file.id})">Hapus</button>
                                    </td>
                                </tr>
                            `);
                        });
                    }
                },
                error: function() {
                    console.error('Gagal memuat file nasabah.');
                }
            });
        }

        $('#modal-btn').on('click', function(event) {
            event.preventDefault();

            var namaNasabah = $('#nama_nasabah').val();

            if (namaNasabah === "") {
                alert("Pilih nama nasabah terlebih dahulu");
            } else {
                $('#modalForm').modal('show');
            }
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
            let url = `{{ route('uploadFile') }}`;
            let method = 'POST';

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let formData = new FormData(this);
            formData.append('id', id);
            formData.append('id_customer', id);
            formData.append('_method', method);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res.success) {
                        audio.play();
                        let msg = "Pengaturan berhasil diupdate!";
                        toastr.success(msg, "BERHASIL", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });
                        $('#modalForm').modal('hide');
                        loadTableFiles(nasabahId);
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
                    btnText.text('Simpan Pengaturan');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        function viewFile(folder, filename, nama_file) {
            var fileExt = filename.split('.').pop().toLowerCase();
            var fileUrl = '/berkas_user/' + filename;

            let content = '';

            if (['pdf'].includes(fileExt)) {
                content = `<iframe src="${fileUrl}" width="100%" height="500px"></iframe>`;
            } else {
                content = `<img src="${fileUrl}" class="img-fluid" />`;;
            }

            Swal.fire({
                title: 'Preview File',
                html: content,
                width: 600
            });
        }

        function deleteFile(id_file) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'File akan dihapus secara permanen!',
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
                            url: `{{ route('deleteFile', ':id') }}`.replace(':id', id_file),
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function() {
                                loadTableFiles(nasabahId);

                                audio.play();
                                toastr.success('File berhasil dihapus!', 'BERHASIL', {
                                    progressBar: true,
                                    timeOut: 3500,
                                    positionClass: "toast-bottom-right"
                                });
                                Swal.close();
                            },
                            error: function() {
                                audio.play();
                                toastr.error('Gagal menghapus file.', 'GAGAL!', {
                                    progressBar: true,
                                    timeOut: 3500,
                                    positionClass: "toast-bottom-right"
                                });

                                btnText.innerHTML = 'Ya, Hapus';
                                confirmBtn.disabled = false;
                            }
                        });
                    });
                }
            });
        }

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            // Hapus validasi error
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let submitBtn = $('#submitBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.addClass('d-none');
            btnText.text('Simpan');
            submitBtn.prop('disabled', false);
        });
    </script>
@endpush
