@extends('admin.layout')
@section('page_title', 'Data Transfer Antar Rekening')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Keuangan</p><h1>Data Transfer Antar Rekening</h1><p class="workspace-description">Catat perpindahan saldo antar rekening.</p></div>
                        <div class="d-flex align-items-center">
                            @if (isset($permissions['tambah']) && $permissions['tambah'] == 1)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalForm">
                                    <i class="bi bi-plus-lg"></i> Tambah Transfer Antar Rekening
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th width="30px">No</th>
                                    <th>Tanggal</th>
                                    <th>Rek. Asal</th>
                                    <th>Rek. Tujuan</th>
                                    <th>Nominal</th>
                                    <th>Keterangan</th>
                                    <th width="100px" class="text-center">Aksi</th>
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
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="myModalLabel160">Transfer Antar Rekening
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formData" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="primary_id" name="primary_id">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="tanggal" class="col-sm-3 col-form-label">Tanggal</label>
                            <div class="col-sm-3">
                                <input type="date" class="form-control" id="tanggal" name="tanggal">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="no_rek" class="col-sm-3 col-form-label">Rek. Asal</label>
                            <div class="col-sm-3">
                                <select class="form-select select-rekening" name="rekening_asal" id="rekening_asal">
                                    <option value=""></option>
                                    @foreach ($bankList as $data)
                                        <option value="{{ $data->id }}">{{ $data->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="no_rek" class="col-sm-2 col-form-label">Rek. Tujuan</label>
                            <div class="col-sm-3">
                                <select class="form-select select-rekening" name="rekening_tujuan" id="rekening_tujuan">
                                    <option value=""></option>
                                    @foreach ($bankList as $data)
                                        <option value="{{ $data->id }}">{{ $data->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="nominal" class="col-sm-3 col-form-label">Nominal</label>
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" class="form-control" id="nominal" name="nominal">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Lampiran</label>
                            <div class="col-sm-8">
                                <input type="file" name="lampiran" id="lampiran"
                                    accept=".png, .jpg, .jpeg, .webp, .pdf">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-8">
                                <div class="img-thumbnail mb-2 d-flex align-items-center justify-content-center"
                                    id="previewFoto"
                                    style="max-width: 140px; height: 150px; background-color: #f8f9fa; border: 1px solid #dee2e6; overflow: hidden;">
                                    <span style="color: #6c757d;">Tidak ada File</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="catatan" class="col-sm-3 col-form-label">Keterangan</label>
                            <div class="col-sm-6">
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
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
            $('.select-rekening').select2({
                dropdownParent: $('#modalForm'),
                width: '100%',
                placeholder: 'Pilih Rekening',
                allowClear: true,
                minimumResultsForSearch: Infinity,
            });

            if ($('body').hasClass('dark')) {
                $('.select2-container').addClass('select2-dark');
            }
        });

        $('#lampiran').on('change', function() {
            const file = this.files[0];
            const previewDiv = $('#previewFoto');

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewDiv.html(
                        `<img src="${e.target.result}" style="max-width: 100%; max-height: 100%;">`);
                };
                reader.readAsDataURL(file);
            } else {
                previewDiv.html('<span style="color: #6c757d;">Tidak ada File</span>');
            }
        });

        var audio = new Audio('{{ asset('audio/notification.ogg') }}');
        var permissions = @json($permissions);
        var showActionColumn = (permissions['edit'] == 1 || permissions['hapus'] == 1);

        $(function() {
            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                ajax: "{{ route('mutasi-saldo.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal',
                        searchable: false
                    },
                    {
                        data: 'rekening_asal',
                        name: 'rekening_asal',
                        searchable: false
                    },
                    {
                        data: 'rekening_tujuan',
                        name: 'rekening_tujuan',
                        searchable: false
                    },
                    {
                        data: 'nominal',
                        name: 'nominal',
                        searchable: false
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: showActionColumn,
                        className: 'text-center'
                    },
                ],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }]
            });
        });

        // Tombol edit
        $(document).on('click', '.edit-button', function() {
            var url = $(this).data('url');
            $.get(url, function(response) {
                if (response.status === 'success') {
                    $('#primary_id').val(response.data.id);
                    $('#tanggal').val(response.data.tanggal);
                    $('#rekening_asal').val(response.data.rekening_asal).trigger('change');
                    $('#rekening_tujuan').val(response.data.rekening_tujuan).trigger('change');
                    let nominal = parseFloat(response.data.nominal);
                    if (!isNaN(nominal)) {
                        $('#nominal').val(nominal.toLocaleString('id-ID'));
                    } else {
                        $('#nominal').val('');
                    }
                    let lampiran = response.data.lampiran;
                    let preview = $('#previewFoto');
                    if (lampiran) {
                        let imageUrl = '/assets/keuangan/mutasi_saldo/' + lampiran;
                        preview.html(
                            `<img src="${imageUrl}" alt="File" style="max-height: 100%; max-width: 100%;">`
                        );
                    } else {
                        preview.html(`<span style="color: #6c757d;">Tidak ada File</span>`);
                    }

                    $('#keterangan').val(response.data.keterangan);

                    $('#modalForm').modal('show');
                }
            });
        });

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            $('#primary_id').val('');
            $('#rekening_asal').val('').trigger('change');
            $('#rekening_tujuan').val('').trigger('change');
            $('#lihat-lampiran-wrapper').addClass('d-none');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            let submitBtn = $('#submitBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.addClass('d-none');
            btnText.text('Simpan');
            submitBtn.prop('disabled', false);

            $('#previewFoto').html(`<span style="color: #6c757d;">Tidak ada File</span>`);
        });

        // Simpan / Update data
        $('#formData').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#submitBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            let id = $('#primary_id').val();
            let url = id ? '{{ route('mutasi-saldo.update', ['mutasi_saldo' => ':id']) }}'.replace(':id', id) :
                '{{ route('mutasi-saldo.store') }}';
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
                success: function() {
                    $('#modalForm').modal('hide');
                    audio.play();
                    let msg = id ? "Transfer Antar Rekening berhasil diupdate!" :
                        "Transfer Antar Rekening berhasil ditambahkan!";
                    toastr.success(msg, "BERHASIL", {
                        progressBar: true,
                        timeOut: 3500,
                        positionClass: "toast-bottom-right",
                    });
                    $('.data-table').DataTable().ajax.reload();
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

                        spinner.addClass('d-none');
                        btnText.text('Simpan');
                        submitBtn.prop('disabled', false);
                    }
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
                            `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menghapus...`;
                        confirmBtn.disabled = true;

                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: form.serialize(),
                            success: function() {
                                audio.play();
                                toastr.success("Data telah dihapus!", "BERHASIL", {
                                    progressBar: true,
                                    timeOut: 3500,
                                    positionClass: "toast-bottom-right"
                                });

                                $('.data-table').DataTable().ajax.reload(null,
                                    false);
                                Swal.close();
                            },
                            error: function() {
                                audio.play();
                                toastr.error("Gagal menghapus data.", "GAGAL!", {
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

        const nominalInput = document.getElementById('nominal');

        nominalInput.addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, ''); // Hapus semua karakter selain angka
            this.value = formatRupiah(value);
        });

        function formatRupiah(angka) {
            return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    </script>
@endpush
