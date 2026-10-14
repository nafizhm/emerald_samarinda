@extends('admin.layout')
@section('page_title', 'Profil & Media')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="mb-4">
                <div class="workspace-heading"><p class="dashboard-eyebrow">Pengaturan</p><h1>Profil & Media</h1><p class="workspace-description">Perbarui identitas perusahaan dan media aplikasi.</p></div>

                <ul class="nav nav-pills gap-2" role="tablist">
                    <li class="nav-item"><button class="nav-link {{ request('tab') !== 'media' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#settings-profile" type="button" role="tab"> <i class="bi bi-building me-2"></i>Profil perusahaan</button></li>
                    <li class="nav-item"><button class="nav-link {{ request('tab') === 'media' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#settings-media" type="button" role="tab"><i class="bi bi-images me-2"></i>Media aplikasi <span class="badge bg-light text-primary ms-1">{{ $media->count() }}</span></button></li>
                </ul>
            </div>
            <div class="tab-content">
            <div id="settings-profile" class="tab-pane fade {{ request('tab') !== 'media' ? 'show active' : '' }}" role="tabpanel">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <h3 class="font-weight-bold text-xl">Profil Perusahaan</h3>
                    </div>
                </div>
                <form id="formData">
                    @csrf
                    <div class="card-body">
                        <input type="hidden" value="{{ $data->id }}" name="primary_id" id="primary_id">

                        <div class="row mb-3 align-items-center">
                            <label for="nama_perusahaan" class="col-sm-3 col-form-label">Nama Perusahaan</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan"
                                    value="{{ old('nama_perusahaan', $data->nama_perusahaan ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="email" class="col-sm-3 col-form-label">Email</label>
                            <div class="col-9">
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email', $data->email ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="alamat" class="col-sm-3 col-form-label">Alamat</label>
                            <div class="col-9">
                                <textarea class="form-control" id="alamat" name="alamat" rows="3">{{ old('alamat', $data->alamat ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="telp" class="col-sm-3 col-form-label">No Telepon</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="telp" name="telp"
                                    value="{{ old('telp', $data->telp ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="hape" class="col-sm-3 col-form-label">No Handphone</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="hape" name="hape"
                                    value="{{ old('hape', $data->hape ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="npwp_perusahaan" class="col-sm-3 col-form-label">NPWP</label>
                            <div class="col-9">
                                <input type="text" class="form-control" id="npwp_perusahaan" name="npwp_perusahaan"
                                    value="{{ old('npwp_perusahaan', $data->npwp_perusahaan ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="pesan_jatuh_tempo" class="col-sm-3 col-form-label">Pesan Jatuh Tempo</label>
                            <div class="col-9">
                                <textarea class="form-control" id="pesan_jatuh_tempo" name="pesan_jatuh_tempo" rows="5">{{ old('pesan_jatuh_tempo', $data->pesan_jatuh_tempo ?? '') }}</textarea>

                                <small class="text-danger">
                                    [[nama_customer]] : Nama Pelanggan Jatuh Tempo <br>
                                    [[jumlah_bulan]] : Jumlah Bulan Tunggakan <br>
                                    [[jumlah_tagihan]] : Total Nominal Tagihan
                                </small>
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
            </div>
            </div>
            <div id="settings-media" class="tab-pane fade {{ request('tab') === 'media' ? 'show active' : '' }}" role="tabpanel">
                @include('admin.pengaturan.pengaturan_media.cards')
            </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script>
        var permissions = @json($permissions);
        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

        $('#formData').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#submitBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            let id = $('#primary_id').val();
            let url = '{{ route('pengaturan-profile.update', ['pengaturan_profile' => ':id']) }}'.replace(':id',
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
                        let msg = "Pengaturan berhasil diupdate!";
                        toastr.success(msg, "BERHASIL", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                            onHidden: function() {
                                window.location.href =
                                    '{{ route('pengaturan-profile.index') }}';
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
    </script>
@endpush
