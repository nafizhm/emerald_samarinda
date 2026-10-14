@extends('admin.layout')
@section('page_title', 'Data Unit')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Ringkasan penjualan</p><h1>Data Unit</h1><p class="workspace-description">Telusuri rincian unit dan perkembangan transaksi.</p></div>
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
                        <table class="table data-table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Cluster</th>
                                    <th>Lokasi</th>
                                    <th>Panjang</th>
                                    <th>Lebar</th>
                                    <th>Luas</th>
                                    <th>Harga</th>
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
        $(function() {
            let table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('total.unit') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'nama_cluster',
                        name: 'nama_cluster',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'lokasi',
                        name: 'lokasi',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'panjang',
                        name: 'panjang',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'lebar',
                        name: 'lebar',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'luas',
                        name: 'luas',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'harga',
                        name: 'harga',
                        orderable: true,
                        searchable: false
                    },
                ]
            });
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }
    </script>
@endpush
