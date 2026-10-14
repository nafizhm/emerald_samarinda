@extends('admin.layout')
@section('page_title', 'Data Detail Penjualan')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Ringkasan penjualan</p><h1>Data Detail Penjualan</h1><p class="workspace-description">Telusuri rincian unit dan perkembangan transaksi.</p></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-lg-3 ms-1">
                            <fieldset class="form-group">
                                <select class="form-select select-lokasi" id="id_lokasi" name="id_lokasi">
                                    <option value="0">Semua Lokasi</option>
                                    @foreach ($lokasiList as $lokasi)
                                        <option value="{{ $lokasi->id }}">{{ $lokasi->nama_kavling }}</option>
                                    @endforeach
                                </select>
                            </fieldset>
                        </div>
                    </div>
                    <table class="table data-table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Kode Kavling</th>
                                <th width="20%">Pelanggan</th>
                                <th width="15%">Harga Jual</th>
                                <th width="15%">Harga Sudah Laku</th>
                                <th width="15%">Sudah Terbayar</th>
                                <th width="15%">Belum Terbayar</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <th colspan="3" class="text-end">Total</th>
                                <th id="total_harga_jual">0</th>
                                <th id="total_harga_laku">0</th>
                                <th id="total_sudah_terbayar">0</th>
                                <th id="total_belum_terbayar">0</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select-lokasi').select2({
                dropdownParent: $('body'),
                width: '100%'
            });

            if ($('body').hasClass('dark')) {
                $('.select2-container').addClass('select2-dark');
            }

            function formatRupiah(angka) {
                if (!angka) angka = 0

                return `
        <div class="d-flex justify-content-between harga-format w-100">
            <span>Rp.</span>
            <span>${new Intl.NumberFormat('id-ID').format(angka)}</span>
        </div>
    `
            }

            let table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                responsive: true,
                paging: false,
                lengthChange: false,
                info: false,
                ajax: {
                    url: "{{ route('dashboard.detail-penjualan') }}",
                    data: function(d) {
                        d.id_lokasi = $('#id_lokasi').val();
                    },
                    dataSrc: function(json) {

                        $('#total_harga_jual').html(formatRupiah(json.total_harga_jual))
                        $('#total_harga_laku').html(formatRupiah(json.total_harga_laku))
                        $('#total_sudah_terbayar').html(formatRupiah(json.total_sudah_terbayar))
                        $('#total_belum_terbayar').html(formatRupiah(json.total_belum_terbayar))

                        return json.data
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false
                    },
                    {
                        data: 'kode_kavling',
                        name: 'kode_kavling'
                    },
                    {
                        data: 'customer',
                        name: 'customer',
                        defaultContent: ''
                    },
                    {
                        data: 'harga_jual',
                        name: 'harga_jual',
                    },
                    {
                        data: 'harga_laku',
                        name: 'harga_laku',

                        defaultContent: ''
                    },
                    {
                        data: 'sudah_terbayar',
                        name: 'sudah_terbayar',

                        defaultContent: ''
                    },
                    {
                        data: 'belum_terbayar',
                        name: 'belum_terbayar',

                        defaultContent: ''
                    }
                ]
            });

            $('#id_lokasi').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
