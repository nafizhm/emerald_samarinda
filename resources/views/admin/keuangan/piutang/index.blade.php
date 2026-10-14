@extends('admin.layout')
@section('page_title', 'Data Piutang')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Keuangan</p><h1>Data Piutang</h1><p class="workspace-description">Kelola piutang dan riwayat pembayaran.</p></div>
                        <div class="d-flex align-items-center">
                            @if (isset($permissions['tambah']) && $permissions['tambah'] == 1)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalForm">
                                    <i class="bi bi-plus-lg"></i> Tambah Piutang
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3 justify-content-between">
                        <div class="col-lg-2 ms-1">
                            <fieldset class="form-group">
                                {{-- Ubah ID agar tidak bentrok dengan input form --}}
                                <input type="date" class="form-control" id="filter_tanggal" name="filter_tanggal" required>
                            </fieldset>
                        </div>
                        <div class="col-lg-3 text-end">
                            <a href="{{ route('piutang.rekap') }}" type="button" class="btn btn-warning btn-md">Rekap Data</a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table data-table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Piutang</th>
                                    <th width="250px">Deskripsi</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                    <th>Tanggal Pelunasan</th>
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

    {{-- MODAL FORM (TAMBAH / EDIT) --}}
    <div class="modal fade text-left" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="modalFormLabel">Piutang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formData" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="primary_id" name="primary_id">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="tanggal_piutang" class="col-sm-3 col-form-label">Tanggal Piutang</label>
                            <div class="col-sm-5">
                                <input type="date" class="form-control" id="tanggal_piutang" name="tanggal_piutang"
                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="id_bank" class="col-sm-3 col-form-label">Rekening</label>
                            <div class="col-sm-3">
                                <select class="form-select" name="id_bank" id="id_bank">
                                    <option value=""></option>
                                    @foreach ($bankList as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="nominal" class="col-sm-2 col-form-label">Nominal</label>
                            <div class="col-sm-4">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input name="nominal" id="nominal" class="form-control format-number" type="text">
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
                            <label for="deskripsi" class="col-sm-3 col-form-label">Deskripsi</label>
                            <div class="col-sm-8">
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                            </div>
                        </div>

                        <div id="info-pembayaran" style="display: none;">
                            <div class="form-group row">
                                <label for="sisa_bayar" class="col-sm-3 col-form-label">Sisa Bayar</label>
                                <div class="col-sm-5">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" class="form-control" id="sisa_bayar" name="sisa_bayar"
                                            disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mt-3">
                                <label for="terbayar" class="col-sm-3 col-form-label">Terbayar</label>
                                <div class="col-sm-5">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" class="form-control" id="terbayar" name="terbayar"
                                            disabled>
                                    </div>
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

    {{-- MODAL DETAIL (VIEW ONLY) --}}
    <div class="modal fade text-left" id="modalDetail" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="modalDetailLabel">Detail Piutang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="tanggal_piutang_detail" class="col-sm-3 col-form-label">Tanggal Piutang</label>
                        <div class="col-sm-5">
                            <input type="date" class="form-control" id="tanggal_piutang_detail"
                                name="tanggal_piutang" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="id_bank_detail" class="col-sm-3 col-form-label">Rekening</label>
                        <div class="col-sm-3">
                            <select class="form-select" name="id_bank" id="id_bank_detail" disabled>
                                <option value=""></option>
                                @foreach ($bankList as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label for="nominal_detail" class="col-sm-2 col-form-label">Nominal</label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp.</span>
                                </div>
                                <input name="nominal" id="nominal_detail" class="form-control format-number"
                                    type="text" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Lampiran</label>
                        <div class="col-sm-8">
                            <input type="file" name="lampiran" id="lampiran_detail"
                                accept=".png, .jpg, .jpeg, .webp, .pdf" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label"></label>
                        <div class="col-sm-8">
                            <div class="img-thumbnail mb-2 d-flex align-items-center justify-content-center"
                                id="previewDetail"
                                style="max-width: 140px; height: 150px; background-color: #f8f9fa; border: 1px solid #dee2e6; overflow: hidden;">
                                <span style="color: #6c757d;">Tidak ada File</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="deskripsi_detail" class="col-sm-3 col-form-label">Deskripsi</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="deskripsi_detail" name="deskripsi" rows="3" readonly></textarea>
                        </div>
                    </div>

                    <div id="info-pembayaran-detail" style="display: none;">
                        <div class="form-group row">
                            <label for="sisa_bayar_detail" class="col-sm-3 col-form-label">Sisa Bayar</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" class="form-control" id="sisa_bayar_detail" name="sisa_bayar"
                                        disabled>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label for="terbayar_detail" class="col-sm-3 col-form-label">Terbayar</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" class="form-control" id="terbayar_detail" name="terbayar"
                                        disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <span class="button-text">Tutup</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 untuk Modal Form
            $('#id_bank').select2({
                dropdownParent: $('#modalForm'),
                width: '100%',
                placeholder: 'Pilih Rekening',
                minimumResultsForSearch: Infinity,
            });

            // Inisialisasi Select2 untuk Modal Detail
            $('#id_bank_detail').select2({
                dropdownParent: $('#modalDetail'),
                width: '100%',
                placeholder: 'Pilih Rekening',
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
                ajax: "{{ route('piutang.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'tanggal_piutang',
                        name: 'tanggal_piutang',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'deskripsi',
                        name: 'deskripsi',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'nominal',
                        name: 'nominal',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'tgl_pelunasan',
                        name: 'tgl_pelunasan',
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
                    },
                ],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }]
            });
            $('#filter_tanggal').on('change', function() {
                let tanggal_piutang = $(this).val();
                if (tanggal_piutang) {
                    table.column(1).search(tanggal_piutang).draw();
                } else {
                    table.column(1).search('').draw();
                }
            });
        });

        $(document).on('click', '.edit-button', function() {
            var url = $(this).data('url');
            $.get(url, function(response) {
                if (response.status === 'success') {
                    $('#primary_id').val(response.data.id);
                    $('#tanggal_piutang').val(response.data.tanggal_piutang);
                    $('#deskripsi').val(response.data.deskripsi);

                    let nominal = parseFloat(response.data.nominal);
                    if (!isNaN(nominal)) {
                        $('#nominal').val(nominal.toLocaleString('id-ID'));
                    } else {
                        $('#nominal').val('');
                    }

                    let lampiran = response.data.lampiran;
                    let preview = $('#previewFoto');
                    if (lampiran) {
                        let imageUrl = '/assets/keuangan/piutang/' + lampiran;
                        preview.html(
                            `<img src="${imageUrl}" alt="File" style="max-height: 100%; max-width: 100%;">`
                        );
                    } else {
                        preview.html(`<span style="color: #6c757d;">Tidak ada File</span>`);
                    }

                    const terbayar = parseFloat(response.data.terbayar) || 0;
                    const sisaBayar = parseFloat(response.data.sisa_bayar) || 0;

                    $('#terbayar').val(terbayar.toLocaleString('id-ID'));
                    $('#sisa_bayar').val(sisaBayar.toLocaleString('id-ID'));
                    $('#id_bank').val(response.data.id_bank).trigger('change');

                    $('#info-pembayaran').show();

                    $('#modalForm').modal('show');
                }
            });
        });

        $(document).on('click', '.detail-button', function() {
            var url = $(this).data('url');
            $.get(url, function(response) {
                if (response.status === 'success') {
                    $('#tanggal_piutang_detail').val(response.data.tanggal_piutang);
                    $('#deskripsi_detail').val(response.data.deskripsi);

                    let nominal = parseFloat(response.data.nominal);
                    if (!isNaN(nominal)) {
                        $('#nominal_detail').val(nominal.toLocaleString('id-ID'));
                    } else {
                        $('#nominal_detail').val('');
                    }

                    let lampiran = response.data.lampiran;
                    let preview = $('#previewDetail');
                    if (lampiran) {
                        let imageUrl = '/assets/keuangan/piutang/' + lampiran;
                        preview.html(
                            `<img src="${imageUrl}" alt="File" style="max-height: 100%; max-width: 100%;">`
                        );
                    } else {
                        preview.html(`<span style="color: #6c757d;">Tidak ada File</span>`);
                    }

                    const terbayar = parseFloat(response.data.terbayar) || 0;
                    const sisaBayar = parseFloat(response.data.sisa_bayar) || 0;

                    $('#terbayar_detail').val(terbayar.toLocaleString('id-ID'));
                    $('#sisa_bayar_detail').val(sisaBayar.toLocaleString('id-ID'));
                    $('#id_bank_detail').val(response.data.id_bank).trigger('change');

                    $('#info-pembayaran-detail').show();

                    $('#modalDetail').modal('show');
                }
            });
        });

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            $('#primary_id').val('');
            $('#id_bank').val('').trigger('change');
            $('#info-pembayaran').hide();
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

        $('#modalDetail').on('hidden.bs.modal', function() {
            $('#id_bank_detail').val('').trigger('change');
            $('#info-pembayaran-detail').hide();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            $('#previewDetail').html(`<span style="color: #6c757d;">Tidak ada File</span>`);
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
            let url = id ? '{{ route('piutang.update', ['piutang' => ':id']) }}'.replace(':id', id) :
                '{{ route('piutang.store') }}';
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
                    let msg = id ? "Piutang berhasil diupdate!" : "Piutang berhasil ditambahkan!";
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

        function formatRupiah(input) {
            let value = input.value.replace(/\D/g, '');
            if (!value) {
                input.value = '';
                return;
            }
            input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    </script>
@endpush