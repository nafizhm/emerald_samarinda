@extends('admin.layout')
@section('page_title', 'Data Pengeluaran')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Keuangan</p><h1>Data Pengeluaran</h1><p class="workspace-description">Pantau dan catat transaksi pengeluaran.</p></div>
                        <div class="d-flex align-items-center">
                            @if (isset($permissions['tambah']) && $permissions['tambah'] == 1)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalForm">
                                    <i class="bi bi-plus-lg"></i> Tambah Pengeluaran
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-lg-2 ms-1">
                            <fieldset class="form-group">
                                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            </fieldset>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped data-table w-100">
                        <thead>
                            <tr>
                                <th width="30px">No</th>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Kategori</th>
                                <th>Rekening</th>
                                <th width="100px" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade text-left" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="modalFormLabel">Pengeluaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formData" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="primary_id" name="primary_id">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="tanggal" class="col-sm-3 col-form-label">Tanggal Pengeluaran</label>
                            <div class="col-sm-3">
                                <input type="date" class="form-control" id="tanggal_input" name="tanggal"
                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="nominal" class="col-sm-3 col-form-label">Nominal</label>
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" class="form-control format-number" id="nominal" name="nominal">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="keterangan" class="col-sm-3 col-form-label">Keterangan</label>
                            <div class="col-sm-9">
                                <textarea name="keterangan" id="keterangan" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="id_kategori_transaksi" class="col-sm-3 col-form-label">Kategori Transaksi</label>
                            <div class="col-sm-6">
                                <select class="form-select" name="id_kategori_transaksi" id="id_kategori_transaksi">
                                    <option value=""></option>
                                    @foreach ($kategoriTransaksi as $item)
                                        <option value="{{ $item->id }}">{{ $item->kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" id="row-hutang" style="display: none;">
                            <label for="id_hutang" class="col-sm-3 col-form-label">Hutang</label>
                            <div class="col-sm-3">
                                <select class="form-select" name="id_hutang" id="id_hutang">
                                    <option value=""></option>
                                    @foreach ($HutangList as $item)
                                        <option value="{{ $item->id }}">{{ $item->deskripsi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="sisa_bayar" class="col-sm-2 col-form-label">Sisa Bayar</label>
                            <div class="col-sm-4">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" class="form-control" id="sisa_bayar" name="sisa_bayar"
                                        disabled>
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
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade text-left" id="modalDetail" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header ">
                    <h5 class="modal-title" id="modalDetailLabel">Detail Pengeluaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <input type="hidden" id="primary_id_detail" name="primary_id">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="tanggal_detail" class="col-sm-3 col-form-label">Tanggal Pengeluaran</label>
                        <div class="col-sm-3">
                            <input type="date" class="form-control" id="tanggal_detail" name="tanggal" readonly>
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
                                <input type="text" class="form-control format-number" id="nominal_detail"
                                    name="nominal" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row" id="rowLampiranDetail" style="display: none;">
                        <label class="col-sm-3 col-form-label">Lampiran</label>
                        <div class="col-sm-8">
                            <div class="img-thumbnail mb-2 d-flex align-items-center justify-content-center"
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

                    <div class="form-group row">
                        <label for="id_kategori_transaksi_detail" class="col-sm-3 col-form-label">Kategori
                            Transaksi</label>
                        <div class="col-sm-6">
                            <select class="form-select" name="id_kategori_transaksi" id="id_kategori_transaksi_detail"
                                disabled>
                                <option value=""></option>
                                @foreach ($kategoriTransaksiDetail as $item)
                                    <option value="{{ $item->id }}">{{ $item->kategori }}</option>
                                @endforeach
                            </select>
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
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#id_kategori_transaksi').select2({
                dropdownParent: $('#modalForm'),
                width: '100%',
                placeholder: "Pilih Kategori Transaksi",
            });
            $('#id_hutang').select2({
                dropdownParent: $('#modalForm'),
                width: '100%',
                placeholder: "Pilih Hutang",
            });

            $('#id_bank_detail').select2({
                dropdownParent: $('#modalDetail'),
                width: '100%',
                placeholder: "Pilih Bank",
            });
            $('#id_kategori_transaksi_detail').select2({
                dropdownParent: $('#modalDetail'),
                width: '100%',
                placeholder: "Pilih Kategori Transaksi",
            });

            if ($('body').hasClass('dark')) {
                $('.select2-container').addClass('select2-dark');
            }
        });

        $(document).ready(function() {
            $('#id_kategori_transaksi').on('change', function() {
                const selectedValue = $(this).val();

                if (selectedValue == 10) {
                    $('#row-hutang').show();
                    $('#sisa_bayar').val('');
                } else {
                    $('#row-hutang').hide();
                    $('#id_hutang').val('');
                    $('#sisa_bayar').val('');
                }
            });

            $('#id_hutang').on('change', function() {
                const idHutang = $(this).val();

                if (idHutang) {
                    $.ajax({
                        url: `/admin/keuangan/hutang/sisa-bayar/${idHutang}`,
                        type: 'GET',
                        success: function(response) {
                            const nominal = response.sisa_bayar || 0;
                            $('#sisa_bayar').val(formatNumber(nominal));
                        },
                        error: function() {
                            $('#sisa_bayar').val('');
                        }
                    });
                } else {
                    $('#sisa_bayar').val('');
                }
            });
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
                ajax: "{{ route('pengeluaran.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal',
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
                        data: 'id_kategori_transaksi',
                        name: 'id_kategori_transaksi',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'rekening',
                        name: 'rekening',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: showActionColumn
                    },
                ],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }]
            });
            $('#tanggal').on('change', function() {
                let tanggal = $(this).val();
                if (tanggal) {
                    table.column(1).search(tanggal).draw();
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
                    $('#tanggal_input').val(response.data.tanggal);
                    $('#keterangan').val(response.data.keterangan);

                    let nominal = parseFloat(response.data.nominal);
                    if (!isNaN(nominal)) {
                        let formattedNominal = nominal.toLocaleString('id-ID');
                        $('#nominal').val(formattedNominal);
                    } else {
                        $('#nominal').val('');
                    }


                    const kategori = response.data.id_kategori_transaksi;
                    $('#id_kategori_transaksi').val(kategori).trigger('change');

                    if (kategori == 10) {
                        $('#id_kategori_transaksi').prop('disabled', true);
                        $('#id_hutang').val(response.data.id_hutang).trigger('change');
                    } else {
                        $('#id_kategori_transaksi').prop('disabled', false);
                    }
                    $('#id_hutang').prop('disabled', true);
                    $('#modalForm').modal('show');
                }
            });
        });

        $(document).on('click', '.detail-button', function() {
            var url = $(this).data('url');
            $.get(url, function(response) {
                if (response.status === 'success') {
                    $('#tanggal_detail').val(response.data.tanggal);
                    $('#keterangan_detail').val(response.data.keterangan);

                    let nominal = parseFloat(response.data.nominal);
                    if (!isNaN(nominal)) {
                        let formattedNominal = nominal.toLocaleString('id-ID');
                        $('#nominal_detail').val(formattedNominal);
                    } else {
                        $('#nominal_detail').val('');
                    }

                    let lampiran_detail = response.data.lampiran;
                    let previewDetail = $('#previewDetail');
                    let rowLampiran = $('#rowLampiranDetail');
                    if (lampiran_detail && lampiran_detail !== '') {
                        let imageUrl = '/assets/keuangan/pengeluaran/' + lampiran_detail;
                        previewDetail.html(
                            `<img src="${imageUrl}" alt="File" style="max-height: 100%; max-width: 100%;">`
                        );
                        rowLampiran.show();
                    } else {
                        rowLampiran.hide();
                    }

                    $('#id_bank_detail').val(response.data.id_bank).trigger('change');
                    const kategori = response.data.id_kategori_transaksi;
                    $('#id_kategori_transaksi_detail').val(kategori).trigger('change');

                    $('#modalDetail').modal('show');
                }
            });
        });

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            $('#primary_id').val('');
            $('#id_kategori_transaksi').val('').trigger('change');
            $('#id_kategori_transaksi').prop('disabled', false);
            $('#id_hutang').val('').trigger('change');
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
            $('#id_kategori_transaksi_detail').val('').trigger('change');
            $('#id_bank_detail').val('').trigger('change');
            $('#previewDetail').html('');
            $('#rowLampiranDetail').hide();
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
            let url = id ? '{{ route('pengeluaran.update', ['pengeluaran' => ':id']) }}'.replace(':id', id) :
                '{{ route('pengeluaran.store') }}';
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
                    let msg = id ? "Pengeluaran berhasil diupdate!" :
                        "Pengeluaran berhasil ditambahkan!";
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
                            `<span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> Menghapus...`;
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

        function formatNumber(num) {
            return parseInt(num).toLocaleString('id-ID');
        }
    </script>
@endpush
