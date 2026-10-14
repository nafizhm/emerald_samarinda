@extends('admin.layout')
@section('page_title', 'Data Foto Unit Tanah Kavling')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Proyek & kavling</p><h1>Data Foto Unit Tanah Kavling</h1><p class="workspace-description">Kelola informasi kavling dan lampiran unit.</p></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-body">

                        <div class="row mb-3">
                            <label class="control-label col-md-2">Lokasi Kavling</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control"
                                    value="{{ $kavling->lokasi->nama_kavling ?? '-' }}" disabled>
                            </div>
                            <label class="control-label col-md-2">Lokasi Kav/Blok</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="{{ $kavling->kode_kavling }}" disabled>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="control-label col-md-2"></label>
                            <div class="col-md-3">
                                <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalForm"><i class="fa fa-upload"></i>
                                    Upload File</a>&nbsp;
                            </div>
                        </div>
                        <hr>
                        <h4>Daftar Foto</h4>
                        <div class="row">
                            @foreach ($fotos as $foto)
                                <div class="col-md-3 mb-3 text-center">
                                    <img src="{{ asset('foto_kavling/' . $foto->lampiran) }}" class="img-fluid mb-2"
                                        alt="Foto"
                                        style="max-height:200px; object-fit:cover; border:1px solid #ddd; padding:5px;">
                                    <form action="{{ route('kavling.lampiran.destroy', $foto->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-delete delete-button">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
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
                        <input type="hidden" id="primary_id" name="primary_id" value="{{ $kavling->id }}">

                        <div class="row mb-3">
                            <label class="control-label col-md-3">Tanggal Upload</label>
                            <div class="col-md-3">
                                <input name="tanggal" id="tanggal" class="form-control" type="date"
                                    value="{{ date('Y-m-d') }}">
                                <span class="help-block"></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="control-label col-md-3">Nama File</label>
                            <div class="col-md-5">
                                <input name="nama_file" id="nama_file"
                                    class="form-control  @error('nama_file') is-invalid @enderror" type="text">
                                @error('nama_file')
                                    <strong class="invalid-feedback ">{{ $message }}</strong>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="control-label col-md-3">File</label>
                            <div class="col-md-3">
                                <input name="lampiran" id="lampiran" type="file"
                                    class="@error('lampiran') is-invalid @enderror">
                                @error('lampiran')
                                    <strong class="invalid-feedback ">{{ $message }}</strong>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="control-label col-md-3">Keterangan File</label>
                            <div class="col-md-8">
                                <input name="keterangan" id="keterangan"
                                    class="form-control @error('keterangan') is-invalid @enderror" type="text">
                                @error('keterangan')
                                    <strong class="invalid-feedback ">{{ $message }}</strong>
                                @enderror
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
        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#tanggal').val('{{ date('Y-m-d') }}');
            $('#nama_file').val('');
            $('#lampiran').val('');
            $('#keterangan').val('');

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
            let url = `{{ route('kavling.lampiran.upload') }}`;
            let method = 'POST';

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let formData = new FormData(this);
            formData.append('id', id);
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
                            onHidden: function() {
                                window.location.href =
                                    `{{ route('kavling.lampiran', ':id') }}`
                                    .replace(':id', id);
                            }
                        });
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
                                            positionClass: "toast-bottom-right",
                                            onHidden: function() {
                                                window.location.href =
                                                    `{{ route('kavling.lampiran', ':id') }}`
                                                    .replace(':id', id);
                                            }
                                        });
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
