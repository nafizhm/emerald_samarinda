@extends('admin.layout')
@section('page_title', 'Detail Proyek')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Proyek & kavling</p><h1>Upload Data Kavling - {{ $nama_kavling }}</h1><p class="workspace-description">Kelola data kavling dan impor berkas pada proyek ini.</p></div>
                        <div class="d-flex align-items-center">
                            <a href="{{ route('lokasi-kavling.index') }}" class="btn btn-sm btn-secondary"> Kembali
                            </a>

                            <a href="{{ route('lokasi-kavling.export', $id) }}" class="btn btn-primary btn-sm ms-2">
                                <i class="fas fa-plus"></i> Download Data
                            </a>

                            <a href="javascript:void(0);" onclick="reloadTable()"
                                class="btn btn-light btn-rounded btn-sm ms-2" title="Perbarui daftar">
                                <i class="bi bi-arrow-clockwise"></i> Perbarui
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <input type="hidden" id="lokasi_id" name="lokasi_id" value="{{ $id }}">
                    <div class="table-responsive">
                        <table class="table data-table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Lokasi</th>
                                    <th>Panjang</th>
                                    <th>Lebar</th>
                                    <th>Luas</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                    <th class="text-center" width="100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
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
                                <label for="id_lokasi" class="col-sm-3 col-form-label">Lokasi / Cluster</label>
                                <div class="col-sm-6">
                                    <input type="text" name="id_lokasi" id="id_lokasi" class="form-control" readonly
                                        required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="kode_kavling" class="col-sm-3 col-form-label">Kode Kavling</label>
                                <div class="col-sm-6">
                                    <input type="text" name="kode_kavling" id="kode_kavling" class="form-control"
                                        readonly required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="panjang_kanan" class="col-sm-3 col-form-label">Panjang Kanan</label>
                                <div class="col-sm-2">
                                    <input type="number" name="panjang_kanan" id="panjang_kanan" class="form-control">
                                </div>

                                <label for="panjang_kiri" class="col-sm-2 col-form-label">Panjang Kiri</label>
                                <div class="col-sm-2">
                                    <input type="number" name="panjang_kiri" id="panjang_kiri" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="lebar_depan" class="col-sm-3 col-form-label">Lebar Depan</label>
                                <div class="col-sm-2">
                                    <input type="number" name="lebar_depan" id="lebar_depan" class="form-control">
                                </div>

                                <label for="lebar_belakang" class="col-sm-2 col-form-label">Lebar Belakang</label>
                                <div class="col-sm-2">
                                    <input type="number" name="lebar_belakang" id="lebar_belakang" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="luas_tanah" class="col-sm-3 col-form-label">Luas Tanah</label>
                                <div class="col-sm-2">
                                    <input type="number" name="luas_tanah" id="luas_tanah" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="hrg_meter" class="col-sm-3 col-form-label">Harga Per Meter</label>
                                <div class="col-sm-4">
                                    <input type="text" name="hrg_meter" id="hrg_meter"
                                        class="form-control format-number">
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label for="hrg_jual" class="col-sm-3 col-form-label">Harga Jual</label>
                                <div class="col-sm-4">
                                    <input type="text" name="hrg_jual" id="hrg_jual"
                                        class="form-control format-number">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="keterangan" class="col-sm-3 col-form-label">Keterangan</label>
                                <div class="col-sm-4">
                                    <input type="text" name="keterangan" id="keterangan" class="form-control">
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
            let lokasi_id = $('#lokasi_id').val();

            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                ajax: `/admin/master/lokasi-kavling/${lokasi_id}/detail`,
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'kode_kavling',
                        name: 'kode_kavling'
                    },
                    {
                        data: null,
                        name: 'panjang_kanan',
                        render: function(data, type, row) {
                            return 'pjg kanan: <b>' + parseFloat(row.panjang_kanan).toFixed(1) +
                                '</b> m pjg kiri: <b>' + parseFloat(row.panjang_kiri).toFixed(1) +
                                '</b> m';
                        }
                    },
                    {
                        data: null,
                        name: 'lebar_depan',
                        render: function(data, type, row) {
                            return 'lbr depan: <b>' + parseFloat(row.lebar_depan).toFixed(1) +
                                '</b> m lbr belakang: <b>' + parseFloat(row.lebar_belakang).toFixed(
                                    1) + '</b> m';
                        }
                    },
                    {
                        data: 'luas_tanah',
                        name: 'luas_tanah',
                        render: function(data) {
                            return data + ' meter';
                        }
                    },
                    {
                        data: 'hrg_jual',
                        name: 'hrg_jual',
                        render: function(data) {
                            return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                        }
                    },
                    {
                        data: 'status_progres',
                        name: 'status_progres',
                        render: function(data) {
                            if (data) {
                                let badgeClass = data.warna_bootstrap ? 'bg-' + data
                                    .warna_bootstrap : '';
                                let style = data.warna ? 'style="background-color: ' + data.warna +
                                    ' !important; color: #000;"' : '';

                                return `<span class="badge ${badgeClass}" ${style}>${data.name}</span>`;
                            }
                            return '<span class="badge bg-secondary">Unknown</span>';
                        }
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
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

        function reloadTable() {
            table.ajax.reload(null, false);
        }

        // Tombol edit
        $(document).on('click', '.edit-button', function() {
            var url = $(this).data('url');
            $.get(url, function(response) {
                if (response.success) {
                    console.log(response.data);
                    $('#primary_id').val(response.data.id);
                    $('#id_lokasi').val(response.data.id_lokasi);
                    $('#kode_kavling').val(response.data.kode_kavling);
                    $('#panjang_kanan').val(response.data.panjang_kanan);
                    $('#panjang_kiri').val(response.data.panjang_kiri);
                    $('#lebar_depan').val(response.data.lebar_depan);
                    $('#lebar_belakang').val(response.data.lebar_belakang);
                    $('#luas_tanah').val(response.data.luas_tanah);
                    $('#hrg_meter').val(response.data.hrg_meter);
                    $('#hrg_jual').val(Number(response.data.hrg_jual).toLocaleString('id-ID'));
                    $('#keterangan').val(response.data.keterangan);

                    $('#modalForm').modal('show');
                }
            });
        });

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            $('#primary_id').val('');
            $('#id_lokasi').val('');
            $('#kode_kavling').val('');
            $('#panjang_kanan').val('');
            $('#panjang_kiri').val('');
            $('#lebar_depan').val('');
            $('#lebar_belakang').val('');
            $('#luas_tanah').val('');
            $('#hrg_meter').val('');
            $('#hrg_jual').val('');
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
            let url = '{{ route('lokasi-kavling.updateDetail', ['id' => ':id']) }}'.replace(':id', id);
            let method = 'PUT';

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let formData = new FormData(this);
            formData.append('_method', method);

            let hrg_jual_raw = $('#hrg_jual').val().replace(/\./g, '');
            formData.set('hrg_jual', hrg_jual_raw);

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
    </script>
@endpush
