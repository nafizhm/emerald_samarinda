@extends('admin.layout')
@section('page_title', 'Dokumen Customer')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading">
                            <p class="dashboard-eyebrow">Dokumen customer</p>
                            <h1>Dokumen Customer</h1>
                            <p class="workspace-description">Kelola berkas dan lampiran customer.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <input type="hidden" name="id" value="{{ $data ? $data->id : '' }}">

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label">Nama Customer</label>
                        <div class="col-sm-3">
                            <select class="form-control select-nasabah" id="nama_nasabah" name="nama_nasabah">
                                <option value="">Pilih Customer</option>
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->kode_customer }} - {{ $c->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label">No. KTP</label>
                        <div class="col-sm-3">
                            <input type="text" name="no_ktp" class="form-control" disabled>
                        </div>
                        <label class="col-sm-2 col-form-label">No. Telp</label>
                        <div class="col-sm-3">
                            <input type="text" name="no_telp" class="form-control" disabled>
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
                            <button type="button" class="btn btn-primary mb-3" id="modal-button">
                                <i class="bi bi-upload"></i> Upload File
                            </button>
                        </div>
                    @endif

                    <table id="data-table" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenis File</th>
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
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-focus="false">

        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content ">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title white" id="myModalLabel160">Upload File Customer</h5>
                    <button type="button" class="close white" data-bs-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <form id="formData" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="primary_id" name="primary_id">

                        <div class="form-group row">
                            <label for="tanggal" class="col-sm-3 col-form-label">Tanggal Upload</label>
                            <div class="col-sm-6">
                                <input type="date" class="form-control" id="tanggal" name="tanggal"
                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="jenis_file" class="col-sm-3 col-form-label">Jenis File</label>
                            <div class="col-sm-6">
                                <select name="jenis_file" id="jenis_file" class="form-select nama-file">
                                    <option value="">Pilih Jenis File</option>
                                    <option value="KTP">Foto KTP</option>
                                    <option value="KK">Foto Kartu Keluarga</option>
                                    <option value="Pemohon">Foto Pemohon</option>
                                    <option value="KTP Pasangan">Foto KTP Pasangan</option>
                                    <option value="NPWP">Foto NPWP</option>
                                    <option value="BPJS">Foto BPJS</option>
                                    <option value="Bukti Bayar">Bukti Bayar</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="lampiran" class="col-sm-3 col-form-label">File Lampiran</label>
                            <div class="col-sm-9">
                                <input type="file" class="form-control" id="lampiran" name="lampiran"
                                    accept=".pdf,.jpg,.jpeg,.png">
                                <div class="mt-2">
                                    <div class="img-thumbnail border d-flex align-items-center justify-content-center"
                                        id="previewLampiran"
                                        style="max-width: 140px; height: 150px; background-color: #f8f9fa; border: 1px solid #dee2e6; overflow: hidden;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="keterangan" class="col-sm-3 col-form-label">Keterangan</label>
                            <div class="col-sm-9">
                                <textarea name="keterangan" id="keterangan" class="form-control" rows="2"></textarea>
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
                            <span class="button-text">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL (VIEW ONLY) --}}
    <div class="modal fade text-left" id="modalDetail" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-focus="false">

        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title white" id="modalDetailLabel">Detail File Customer</h5>
                    <button type="button" class="close white" data-bs-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <input type="hidden" id="primary_id_detail" name="primary_id">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="tanggal_detail" class="col-sm-3 col-form-label">Tanggal Upload</label>
                        <div class="col-sm-3">
                            <input type="date" class="form-control" id="tanggal_detail" name="tanggal" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="nama_file_detail" class="col-sm-3 col-form-label">Jenis File</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="nama_file_detail" name="nama_file" readonly>
                        </div>
                    </div>
                    <div class="form-group row" id="rowLampiranDetail" style="display: none;">
                        <label class="col-sm-3 col-form-label">File Lampiran</label>
                        <div class="col-sm-8">
                            <div class="img-thumbnail border d-flex align-items-center justify-content-center"
                                id="previewDetail"
                                style="max-width: 140px; height: 150px; background-color: #f8f9fa; border: 1px solid #dee2e6; overflow: hidden;">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="keterangan_detail" class="col-sm-3 col-form-label">Keterangan</label>
                        <div class="col-sm-9">
                            <textarea name="keterangan" id="keterangan_detail" class="form-control" rows="2" readonly></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <span class="button-text">Tutup</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        var permissions = @json($permissions);

        $(document).ready(function() {
            $('.select-nasabah').select2({
                width: '100%',
                placeholder: 'Pilih Customer',
                allowClear: true
            });

            $('.select-nasabah').on('change', function() {
                var nasabahId = $(this).val();

                if (nasabahId) {
                    $.ajax({
                        url: `{{ route('nasabah.details', ':id') }}`.replace(':id', nasabahId),
                        method: 'GET',
                        success: function(response) {
                            let data = response.data;
                            $('input[name="no_ktp"]').val(data.no_ktp || '');
                            $('input[name="no_telp"]').val(data.no_telp || '');
                            $('input[name="lokasi_perumahan"]').val(data.lokasi_perumahan ||
                            '');
                            $('input[name="kode_kavling"]').val(data.lokasi_kav_blok || '');
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

        $('#lampiran').on('change', function() {
            var file = this.files[0];
            var preview = $('#previewLampiran');
            var fileExt = file ? file.name.split('.').pop().toLowerCase() : '';

            if (file && ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.html(
                        `<img src="${e.target.result}" alt="File" style="max-height: 100%; max-width: 100%;">`
                    );
                };
                reader.readAsDataURL(file);
            } else if (file) {
                preview.html(
                    `<div class="text-center text-muted"><i class="bi bi-file-earmark-text"></i><br>${file.name}</div>`
                );
            } else {
                preview.html('');
            }
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
                            let namaFile = file.jenis_file || file.nama_file || '-';
                            let fileUrl = '/assets/berkas_customer/' + file.lampiran;

                            $('#data-table tbody').append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${namaFile}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info detail-button" data-url="/admin/customer/upload-file/detail/${file.id}">View</button>
                                        <a href="${fileUrl}" class="btn btn-sm btn-success" download>Download</a>
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

        $('#modal-button').on('click', function(event) {
            event.preventDefault();

            var namaNasabah = $('#nama_nasabah').val();

            if (!namaNasabah) {
                toastr.warning("Pilih customer terlebih dahulu sebelum upload file!", "PERHATIAN", {
                    progressBar: true,
                    timeOut: 3500,
                    positionClass: "toast-bottom-right",
                });
                return;
            }

            $('#primary_id').val(namaNasabah);
            $('#modalForm').modal('show');
        });

        $('#formData').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#submitBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            let id = $('#nama_nasabah').val() || $('#primary_id').val();
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
                                '<span class="invalid-feedback d-block" role="alert"><strong>' +
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

        function viewFile(filename, nama_file) {
            var fileExt = filename.split('.').pop().toLowerCase();
            var fileUrl = '/assets/berkas_customer/' + filename;
            var preview = $('#previewDetail');
            var rowLampiran = $('#rowLampiranDetail');

            $('#tanggal_detail').val($('#tanggal').val());
            $('#nama_file_detail').val(nama_file);
            $('#keterangan_detail').val($('#keterangan').val());

            if (['pdf'].includes(fileExt)) {
                preview.html(`<iframe src="${fileUrl}" width="100%" height="400px"></iframe>`);
            } else {
                preview.html(`<img src="${fileUrl}" alt="File" style="max-height: 100%; max-width: 100%;">`);
            }
            rowLampiran.show();

            $('#modalDetail').modal('show');
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
            $('#primary_id').val('');
            $('#lampiran').val('');
            $('.form-select').val('').trigger('change');

            $('#previewLampiran').html('');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            let submitBtn = $('#submitBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.addClass('d-none');
            btnText.text('Simpan');
            submitBtn.prop('disabled', false);
        });

        $('#modalDetail').on('hidden.bs.modal', function() {
            $('#previewDetail').html('');
            $('#rowLampiranDetail').hide();
        });

        $(document).on('click', '.detail-button', function() {
            var url = $(this).data('url');
            $.get(url, function(response) {
                if (response.success && response.data) {
                    $('#tanggal_detail').val(response.data.tanggal);
                    $('#nama_file_detail').val(response.data.jenis_file || response.data.nama_file || '');
                    $('#keterangan_detail').val(response.data.keterangan || '');

                    let lampiran = response.data.lampiran;
                    let preview = $('#previewDetail');
                    let rowLampiran = $('#rowLampiranDetail');
                    if (lampiran && lampiran !== '') {
                        let imageUrl = '/assets/berkas_customer/' + lampiran;
                        let fileExt = lampiran.split('.').pop().toLowerCase();

                        if (['pdf'].includes(fileExt)) {
                            preview.html(`<iframe src="${imageUrl}" width="100%" height="400px"></iframe>`);
                        } else {
                            preview.html(
                                `<img src="${imageUrl}" alt="File" style="max-height: 100%; max-width: 100%;">`
                            );
                        }
                        rowLampiran.show();
                    } else {
                        rowLampiran.hide();
                    }

                    $('#modalDetail').modal('show');
                }
            });
        });
    </script>
@endpush
