@extends('admin.layout')
@section('page_title', 'Pengaturan Koneksi WA')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">WhatsApp</p><h1>Pengaturan Koneksi WA</h1><p class="workspace-description">Kelola koneksi dan template pesan pelanggan.</p></div>
                    </div>
                </div>
                <form id="formData">
                    @csrf
                    <div class="card-body">
                        <input type="hidden" value="{{ $data->id ?? '' }}" name="primary_id" id="primary_id">

                        <div class="row mb-3 align-items-center">
                            <label for="url_api" class="col-sm-3 col-form-label">URL API</label>
                            <div class="col-9">
                                <input type="url" class="form-control" id="url_api" name="url_api"
                                    value="{{ old('url_api', $data->url_api ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="api_key" class="col-sm-3 col-form-label">API Key</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="api_key" name="api_key"
                                    value="{{ old('api_key', $data->api_key ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="number_key" class="col-sm-3 col-form-label">Number Key</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="number_key" name="number_key"
                                    value="{{ old('number_key', $data->number_key ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="test_pesan" class="col-sm-3 col-form-label"></label>
                            <div class="col-9">
                                <a href="javascript:void(0)" class="btn btn-primary" id="testPesan" data-bs-toggle="modal"
                                    data-bs-target="#modalForm">Tes Pesan</a>
                            </div>
                        </div>
                    </div>
                    @if (isset($permissions['edit']) && $permissions['edit'] == 1)
                        <div class="card-footer d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                                    aria-hidden="true"></span>
                                <span class="button-text">Simpan Pengaturan</span>
                            </button>
                        </div>
                    @endif
                </form>
        </section>
    </div>

    <div class="modal fade text-left" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-bs-focus="false">

        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="myModalLabel160">Tes Pesan WhatsApp
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <form id="testPesanForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="primary_id" name="primary_id">

                        <div class="row mb-3 align-items-center">
                            <label for="no_wa" class="col-sm-3 col-form-label">No. Whatsapp</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="no_wa" name="no_wa">
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="message" class="col-sm-3 col-form-label">Isi Pesan</label>
                            <div class="col-sm-9">
                                <textarea type="text" class="form-control" id="message" name="message" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <span class="button-text">Batal</span>
                        </button>
                        <button type="submit" class="btn btn-primary ms-1" id="sendButton">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                                aria-hidden="true"></span>
                            <span class="button-text">Kirim Pesan</span>
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

        $('#modalForm').on('show.bs.modal', function() {
            $('#testPesanForm')[0].reset();
            $('#testPesanForm .is-invalid').removeClass('is-invalid');
            $('#testPesanForm .invalid-feedback').remove();

            let sendButton = $('#sendButton');
            sendButton.find('.spinner-border').addClass('d-none');
            sendButton.find('.button-text').text('Kirim Pesan');
            sendButton.prop('disabled', false);
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
            let url = '{{ route('pengaturan-koneksi.update', ['pengaturan_koneksi' => ':id']) }}'.replace(':id',
                id);
            let method = 'PUT';

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
                        let msg = res.message || "Pengaturan koneksi berhasil disimpan!";
                        toastr.success(msg, "BERHASIL", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
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

        $('#testPesanForm').on('submit', function(e) {
            e.preventDefault();

            let sendButton = $('#sendButton');
            let spinner = sendButton.find('.spinner-border');
            let btnText = sendButton.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Mengirim...');
            sendButton.prop('disabled', true);

            let url = '{{ route('kirimPesanWa') }}';
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
                success: function(res) {
                    if (res.success) {
                        audio.play();
                        let msg = res.message || "Pesan berhasil dikirim!";
                        toastr.success(msg, "BERHASIL", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });
                        $('#modalForm').modal('hide');
                    } else {
                        audio.play();
                        toastr.error(res.message || "Pesan gagal dikirim!", "GAGAL!", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
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
                    btnText.text('Kirim Pesan');
                    sendButton.prop('disabled', false);
                }
            });
        });
    </script>
@endpush
