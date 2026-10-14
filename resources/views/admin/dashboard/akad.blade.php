@extends('admin.layout')
@section('page_title', 'Data Akad')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Ringkasan penjualan</p><h1>Data Akad</h1><p class="workspace-description">Telusuri rincian unit dan perkembangan transaksi.</p></div>
                        <div class="d-flex align-items-center">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary align-items-center d-flex btn-sm"><i
                                    class="fas fa-arrow-left mr-2"></i>
                                Kembali</a>
                            <a href="javascript:void(0);" onclick="reloadTable()"
                                class="btn btn-light btn-rounded btn-sm ms-2" title="Perbarui daftar">
                                <i class="bi bi-arrow-clockwise"></i> Perbarui
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-table" class="table table-bordered table-striped table-hover table-sm data-table">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Lokasi Kavling</th>
                                    <th>Status Progres</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script>
        $(function() {
            let table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('akad.unit') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'nama_lengkap',
                        name: 'nama_lengkap'
                    },
                    {
                        data: 'id_lokasi',
                        name: 'id_lokasi'
                    },
                    {
                        data: 'id_status_progres',
                        name: 'id_status_progres'
                    },
                ]
            });
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }
    </script>
@endpush
