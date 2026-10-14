@extends('admin.layout')
@section('page_title', 'Data Lunas')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Ringkasan penjualan</p><h1>Data Lunas</h1><p class="workspace-description">Telusuri rincian unit dan perkembangan transaksi.</p></div>
                        <div class="d-flex align-items-center">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm d-flex align-items-center">
                                <i class="fas fa-arrow-left mr-2"></i> Kembali
                            </a>
                            <a href="javascript:void(0);" onclick="reloadTable()" class="btn btn-light btn-sm ms-2">
                                <i class="bi bi-arrow-clockwise"></i> Perbarui
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-table" class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Lokasi Kavling</th>
                                    <th>Kode Kavling</th>
                                    <th>Harga Jual</th>
                                    <th>Tanggal Lunas</th>
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
            window.table = $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('lunas.unit') }}",
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
                        data: 'perumahan',
                        name: 'perumahan'
                    },
                    {
                        data: 'kode_kavling',
                        name: 'kode_kavling'
                    },
                    {
                        data: 'harga_jual',
                        name: 'harga_jual'
                    },
                    {
                        data: 'tanggal_lunas',
                        name: 'tanggal_lunas'
                    }
                ]
            });
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }
    </script>
@endpush
