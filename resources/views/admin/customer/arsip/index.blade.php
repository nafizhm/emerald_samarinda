@extends('admin.layout')
@section('page_title', 'Data Arsip Pelanggan')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Pelanggan</p><h1>Data Arsip Pelanggan</h1><p class="workspace-description">Telusuri data pelanggan yang telah diarsipkan.</p></div>
                        <div class="d-flex align-items-center">
                            <a href="javascript:void(0);" onclick="reloadTable()" class="btn btn-light btn-rounded btn-sm ms-2"
                                title="Perbarui daftar">
                                <i class="bi bi-arrow-clockwise"></i> Perbarui
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Tanggal Cancel</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Marketing</th>
                                    <th>Lokasi Kavling</th>
                                    <th>Status Progres</th>
                                    <th>Alasan Cancel</th>
                                    <th class="text-center" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script>
        var permissions = @json($permissions);
        var showActionColumn = (permissions['hapus'] == 1);

        $(function() {
            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('arsip-customer.index') }}",
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: 'tgl_cancel',
                    name: 'tgl_cancel',
                    searchable: false
                }, {
                    data: 'nama_lengkap',
                    name: 'nama_lengkap',
                    searchable: true
                }, {
                    data: 'id_marketing',
                    name: 'id_marketing',
                    searchable: false
                }, {
                    data: 'id_lokasi',
                    name: 'id_lokasi',
                    searchable: false
                }, {
                    data: 'id_status_progres',
                    name: 'id_status_progres',
                    className: 'text-center',
                    searchable: false
                }, {
                    data: 'alasan_cancel',
                    name: 'alasan_cancel',
                    searchable: true
                }, {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    visible: showActionColumn
                }],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }]
            });
        });

        $(document).on('click', '.restore-button', function(e) {
            e.preventDefault();

            const id = $(this).data('id');

            Swal.fire({
                title: 'Kembalikan data?',
                text: 'Data customer akan dikembalikan ke daftar aktif',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<span class="swal-btn-text">Ya, Kembalikan</span>',
                cancelButtonText: 'Batal',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-success mx-2',
                    cancelButton: 'btn btn-secondary'
                },
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const confirmBtn = Swal.getConfirmButton();
                    const btnText = confirmBtn.querySelector('.swal-btn-text');
                    btnText.innerHTML = `<span class="spinner-border spinner-border-sm mx-2"></span> Memproses...`;
                    confirmBtn.disabled = true;

                    return $.ajax({
                        url: '{{ route("customer.restore", ":id") }}'.replace(':id', id),
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        }
                    }).then(response => {
                        if (!response.success) {
                            throw new Error(response.message);
                        }
                        return response;
                    }).catch(error => {
                        let msg = 'Terjadi kesalahan';
                        if (error.responseJSON && error.responseJSON.message) {
                            msg = error.responseJSON.message;
                        }
                        Swal.showValidationMessage(msg);
                        btnText.innerHTML = `Ya, Kembalikan`;
                        confirmBtn.disabled = false;
                        throw error;
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    audio.play();
                    toastr.success("Data berhasil dikembalikan!", "BERHASIL", {
                        progressBar: true,
                        timeOut: 3500,
                        positionClass: "toast-bottom-right"
                    });
                    $('.data-table').DataTable().ajax.reload(null, false);
                }
            });
        });
    </script>
@endpush
