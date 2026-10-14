@extends('admin.layout')
@section('page_title', 'Denah Penjualan')
@push('styles')
<link rel="stylesheet" href="{{ asset('template/assets/css/siteplan-custom.css') }}?v={{ filemtime(public_path('template/assets/css/siteplan-custom.css')) }}">
@endpush
@section('content')
@include('admin.st_penjualan.siteplan_page')
    <div class="modal fade text-left" id="modalDetail" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel"
        aria-hidden="true" data-bs-backdrop="static">

        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header unit-detail-header">
                    <div><span class="unit-detail-eyebrow">Rincian unit</span><h5 class="modal-title" id="modalDetailLabel">Detail kavling</h5><p id="unit-detail-subtitle">Informasi tanah dan pelanggan</p></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                
                    <div class="modal-body">
                        <input type="hidden" id="id_lokasi" name="id_lokasi" value="">
                        <input type="hidden" id="id_kavling" name="id_kavling" value="">
                        <div id="unit-detail-state" role="status" class="unit-detail-state">Memuat rincian unit...</div>

                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" class="btn btn-primary btn-sm" id="btn-cetak" disabled>
                                <i class="fas fa-print"></i> Cetak Data
                            </button>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <ul class="nav nav-tabs mb-3" id="tabDetail" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="page-1-tab" data-bs-toggle="pill" href="#page-1"
                                            role="tab" aria-controls="page-1" aria-selected="true">
                                            <i class="bi bi-grid me-2"></i>Data unit
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="page-2-tab" data-bs-toggle="pill" href="#page-2"
                                            role="tab" aria-controls="page-2" aria-selected="false">
                                            <i class="bi bi-person me-2"></i>Data pelanggan
                                        </a>
                                    </li>
                                    {{-- <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="page-3-tab" data-bs-toggle="pill" href="#page-3"
                                            role="tab" aria-controls="page-3" aria-selected="false">
                                            Tagihan & Pembayaran
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="page-4-tab" data-bs-toggle="pill" href="#page-4"
                                            role="tab" aria-controls="page-4" aria-selected="false">
                                            Foto Unit
                                        </a>
                                    </li> --}}
                                </ul>
                                <div class="tab-content" id="tabDetailContent">
                                    <div class="tab-pane fade show active" id="page-1" role="tabpanel" aria-labelledby="page-1-tab">
<section class="unit-detail-section"><h6>Ringkasan unit</h6><dl class="unit-detail-grid"><div class="unit-detail-item unit-detail-wide"><dt>Lokasi kavling</dt><dd id="lokasi_id">-</dd></div>
<div class="unit-detail-item"><dt>Blok / nomor kavling</dt><dd id="kode_kavling_id">-</dd></div>
<div class="unit-detail-item"><dt>Harga jual</dt><dd id="harga_id">-</dd></div>
<div class="unit-detail-item"><dt>Panjang tanah</dt><dd id="panjang_id">-</dd></div>
<div class="unit-detail-item"><dt>Lebar tanah</dt><dd id="lebar_id">-</dd></div>
<div class="unit-detail-item"><dt>Luas tanah</dt><dd id="luas_tanah_id">-</dd></div></dl></section>
<section class="unit-detail-section"><h6>Catatan unit</h6><dl class="unit-detail-grid"><div class="unit-detail-item unit-detail-wide"><dt>Keterangan</dt><dd id="keterangan_id">-</dd></div></dl></section>
</div>
<div class="tab-pane fade" id="page-2" role="tabpanel" aria-labelledby="page-2-tab"><div id="unit-customer-empty" class="unit-detail-state" hidden>Belum ada pelanggan yang dialokasikan untuk kavling ini.</div><div id="unit-customer-content"><section class="unit-detail-section"><h6>Identitas pelanggan</h6><dl class="unit-detail-grid"><div class="unit-detail-item unit-detail-wide"><dt>Nama lengkap</dt><dd id="nama_lengkap_id">-</dd></div>
<div class="unit-detail-item"><dt>Nomor KTP</dt><dd id="no_ktp_id">-</dd></div>
<div class="unit-detail-item"><dt>Nomor KTP pasangan</dt><dd id="no_ktp_p_id">-</dd></div>
<div class="unit-detail-item"><dt>Tempat lahir</dt><dd id="tempat_lahir_id">-</dd></div>
<div class="unit-detail-item"><dt>Tanggal lahir</dt><dd id="tgl_lahir_id">-</dd></div>
<div class="unit-detail-item"><dt>Jenis kelamin</dt><dd id="jenis_kelamin_id">-</dd></div>
<div class="unit-detail-item"><dt>NPWP</dt><dd id="npwp_id">-</dd></div></dl></section>
<section class="unit-detail-section"><h6>Kontak & pembelian</h6><dl class="unit-detail-grid"><div class="unit-detail-item"><dt>Telepon / WhatsApp</dt><dd id="no_telp_id">-</dd></div>
<div class="unit-detail-item"><dt>Jenis pembelian</dt><dd id="jenis_pembelian_id">-</dd></div>
<div class="unit-detail-item unit-detail-wide"><dt>Alamat KTP</dt><dd id="alamat_id">-</dd></div>
<div class="unit-detail-item unit-detail-wide"><dt>Alamat domisili</dt><dd id="alamat_domisili_id">-</dd></div></dl></section>
<section class="unit-detail-section"><h6>Dokumen pelanggan</h6><div class="unit-detail-documents"><div class="unit-detail-document"><span>KTP</span><a href="#" class="view-photo-link" id="fotoKtpLink" target="_blank" rel="noopener" style="display:none"><i class="bi bi-box-arrow-up-right"></i> Lihat dokumen</a><span id="noFotoKtp">Belum tersedia</span></div><div class="unit-detail-document"><span>Pemohon</span><a href="#" class="view-photo-link" id="fotoPemohonLink" target="_blank" rel="noopener" style="display:none"><i class="bi bi-box-arrow-up-right"></i> Lihat dokumen</a><span id="noFotoPemohon">Belum tersedia</span></div><div class="unit-detail-document"><span>Kartu keluarga</span><a href="#" class="view-photo-link" id="fotoKkLink" target="_blank" rel="noopener" style="display:none"><i class="bi bi-box-arrow-up-right"></i> Lihat dokumen</a><span id="noFotoKk">Belum tersedia</span></div><div class="unit-detail-document"><span>KTP pasangan</span><a href="#" class="view-photo-link" id="fotoKtpPasanganLink" target="_blank" rel="noopener" style="display:none"><i class="bi bi-box-arrow-up-right"></i> Lihat dokumen</a><span id="noFotoKtpPasangan">Belum tersedia</span></div><div class="unit-detail-document"><span>NPWP</span><a href="#" class="view-photo-link" id="fotoNpwpLink" target="_blank" rel="noopener" style="display:none"><i class="bi bi-box-arrow-up-right"></i> Lihat dokumen</a><span id="noFotoNpwp">Belum tersedia</span></div><div class="unit-detail-document"><span>BPJS</span><a href="#" class="view-photo-link" id="fotoBpjsLink" target="_blank" rel="noopener" style="display:none"><i class="bi bi-box-arrow-up-right"></i> Lihat dokumen</a><span id="noFotoBpjs">Belum tersedia</span></div></div></section></div></div>
                                    <div class="tab-pane fade" id="page-3">
                                        <h3>Tagihan & Pembayaran</h3>
                                        <hr>
                                        <div id="tagihan-pembayaran-wrapper">
                                            {{-- Tabel Tagihan --}}
                                            <h5>- Tagihan - </h5>
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr class="table-primary">
                                                        <th>No</th>
                                                        <th>Jenis Tagihan</th>
                                                        <th>Harga</th>
                                                        <th>Catatan</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody-tagihan"></tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2" class="text-right">Total</th>
                                                        <th id="total-tagihan">Rp 0</th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                            {{-- Tabel Pembayaran --}}
                                            <h5>- Pemasukan - </h5>
                                            <table class="table table-bordered table-sm mt-3">
                                                <thead>
                                                    <tr class="table-success">
                                                        <th>No</th>
                                                        <th>Tanggal</th>
                                                        <th>Jenis</th>
                                                        <th>Harga</th>
                                                        <th>Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody-pembayaran"></tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3" class="text-right">Total</th>
                                                        <th id="total-pembayaran">Rp 0</th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                            {{-- Tabel Pengeluaran --}}
                                            <h5>- Pengeluaran - </h5>
                                            <table class="table table-bordered table-sm mt-3">
                                                <thead>
                                                    <tr class="table-danger">
                                                        <th>No</th>
                                                        <th>Tanggal</th>
                                                        <th>Jenis</th>
                                                        <th>Harga</th>
                                                        <th>Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody-pengeluaran"></tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3" class="text-right">Total</th>
                                                        <th id="total-pengeluaran">Rp 0</th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                            <div id="sisa-bayar-wrapper" class="mt-3">
                                                <table class="table table-bordered table-sm">
                                                    <tr class="table-warning">
                                                        <th colspan="2" class="text-center">Sisa Bayar</th>
                                                    </tr>
                                                    <tr>
                                                        <td><strong id="sisa-bayar">Rp 0</strong></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="page-4">
                                        <h3>Foto Unit</h3>
                                        <hr>
                                        <div id="foto">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                <div class="modal-footer">
                    <span class="unit-detail-footer-note">Data ditampilkan sesuai catatan unit.</span>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).on('click', '#btn-cetak', function() {
            const data = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id_kavling: $('#id_kavling').val(),
            };

            if (!data.id_kavling || $(this).prop('disabled')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Data tidak lengkap. Pastikan detail sudah dimuat.',
                });
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('st-penjualan.cetak') }}';
            form.target = '_blank';

            for (const key in data) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key] || '';
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        });

        let loadedSiteplans = [];
        const loadingSiteplans = new Set();

        $(document).ready(function() {
            const firstLocationId = $('[data-id-lokasi]').first().data('id-lokasi');
            if (firstLocationId) {
                loadSiteplanData(firstLocationId);
            }

            if ($.fn.select2) $('.select-jk').select2({
                theme: "bootstrap4",
                minimumResultsForSearch: Infinity,
                placeholder: "Pilih Jenis Kelamin",
                width: '100%',
            });

        });

        function loadSiteplanData(idLokasi) {
            if (loadedSiteplans.includes(idLokasi) || loadingSiteplans.has(idLokasi)) {
                return;
            }

            const tabPane = $(`#lokasi-${idLokasi}`);
            const contentDiv = tabPane.find('.siteplan-content');
            loadingSiteplans.add(idLokasi);
            function showLoadError(message) {
                const state = $('<div class="siteplan-state"></div>');
                state.append('<i class="bi bi-map"></i>');
                state.append($('<p></p>').text(message));
                state.append($('<button type="button" class="dashboard-action">Coba lagi</button>').on('click', () => loadSiteplanData(idLokasi)));
                contentDiv.empty().append(state);
            }

            if (!contentDiv.find('.loading-spinner').length) {
                contentDiv.html(`
                    <div class="loading-spinner siteplan-state">
                        <div class="spinner-border text-primary me-2" role="status"></div>
                        <span>Memuat denah...</span>
                    </div>
                `);
            }

            $.ajax({
                url: `{{ route('st-penjualan.load', ['id_lokasi' => ':id']) }}`.replace(':id', idLokasi),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        contentDiv.html(response.html);
                        loadedSiteplans.push(idLokasi);

                        const svgElement = tabPane.find('svg').get(0);
                        const svgContainer = tabPane.find('.svg-container').get(0);
                        const resetButton = tabPane.find('.reset-button').get(0);
                        if (svgElement && svgContainer && resetButton) {
                            initializeSVGControls(svgElement, svgContainer, resetButton);
                        }
                    } else {
                        showLoadError(response.message || 'Denah belum dapat dimuat.');
                    }
                },
                error: function(xhr, status, error) {
                    showLoadError('Denah gagal dimuat. Periksa koneksi dan coba kembali.');
                },
                complete: function() { loadingSiteplans.delete(idLokasi); }
            });
        }

        function initializeSVGInteractions(container) {
            container.find('.reset-button').on('click', function() {
                const svg = container.find('svg');
                if (svg.length) {
                    svg.css('transform', '');
                }
            });
        }

        function loadFotoKavling(id_kavling) {
            $.ajax({
                url: '{{ url('/admin/st-penjualan/foto') }}/' + id_kavling,
                type: 'GET',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#foto').html('<div class="row">' + response.html + '</div>');

                    }
                },
                error: function() {
                    toastr.error('Gagal memuat foto kavling.');
                }
            });
        }

        function toggleLegend() {
            const legend = document.getElementById('legend');
            if (!legend) return;
            const hidden = !legend.hidden;
            legend.hidden = hidden;
            document.getElementById('siteplan-workspace').classList.toggle('legend-collapsed', hidden);
            document.getElementById('legend-toggle').setAttribute('aria-expanded', hidden ? 'false' : 'true');
        }
        $('#modalDetail').on('hidden.bs.modal', function() {
            resetUnitDetail();
        });

        function resetUnitDetail() {
            $('#modalDetail dd').text('-');
            $('#id_kavling, #id_lokasi').val('');
            $('#unit-customer-empty').prop('hidden', true);
            $('#unit-customer-content').prop('hidden', true);
            $('#tabDetailContent').prop('hidden', true);
        }

        let detailRequest = null;
        $(document).on('click', '.detail-button', function() {
            var url = $(this).data('url');
            var id_kavling = $(this).data('id');
            if (detailRequest) detailRequest.abort();
            resetUnitDetail();
            $('#btn-cetak').prop('disabled', true);
            $('#unit-detail-state').text('Memuat rincian unit...').show();
            $('#modalDetailLabel').text('Detail kavling');
            $('#unit-detail-subtitle').text('Informasi tanah dan pelanggan');
            bootstrap.Tab.getOrCreateInstance(document.getElementById('page-1-tab')).show();
            hideAllCustomerPhotoLinks();
            $('#modalDetail').data('id-kavling', id_kavling);

            detailRequest = $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#modalDetailLabel').text('Kavling ' + (response.kavling.kode_kavling ?? '-'));
                        $('#unit-detail-subtitle').text(response.lokasi + ' · ' + (response.customer ? response.customer.nama_lengkap : 'Belum dialokasikan'));
                        $('#unit-detail-state').hide();
                        $('#tabDetailContent').prop('hidden', false);
                        $('#btn-cetak').prop('disabled', false);
                        $('#id_kavling').val(id_kavling);
                        $('#kode_kavling_id').text(response.kavling.kode_kavling ?? '-');
                        $('#panjang_id').text(response.kavling.panjang == null || response.kavling.panjang === '' ? '-' : numberFormat(response.kavling.panjang) + ' m');
                        $('#lebar_id').text(response.kavling.lebar == null || response.kavling.lebar === '' ? '-' : numberFormat(response.kavling.lebar) + ' m');
                        $('#luas_tanah_id').text(response.kavling.luas_tanah == null || response.kavling.luas_tanah === '' ? '-' : numberFormat(response.kavling.luas_tanah) + ' m\u00b2');
                        $('#harga_id').text(response.kavling.hrg_jual == null ? '-' : 'Rp ' + numberFormat(response.kavling.hrg_jual));
                        $('#keterangan_id').text(response.kavling.keterangan ?? '-');

                        $('#unit-customer-empty').prop('hidden', !!response.customer);
                        $('#unit-customer-content').prop('hidden', !response.customer);
                        if (response.customer) {
                            $('#nama_lengkap_id').text(response.customer.nama_lengkap ?? '-');
                            $('#no_ktp_id').text(response.customer.no_ktp ?? '-');
                            $('#no_ktp_p_id').text(response.customer.no_ktp_p ?? '-');
                            $('#tempat_lahir_id').text(response.customer.tempat_lahir ?? '-');
                            $('#tgl_lahir_id').text(formatTanggalIndo(response.customer.tgl_lahir));
                            $('#jenis_kelamin_id').text(response.customer.jenis_kelamin ?? '-');
                            $('#alamat_id').text(response.customer.alamat ?? '-');
                            $('#alamat_domisili_id').text(response.customer.alamat_domisili ?? '-');
                            $('#no_telp_id').text(response.customer.no_telp ?? '-');
                            $('#npwp_id').text(response.customer.npwp ?? '-');
                            $('#jenis_pembelian_id').text(response.customer.jenis_pembelian ?? '-');
                        }
                        $('#id_lokasi').val(response.kavling.id_lokasi);

                        $('#lokasi_id').text(response.lokasi);

                        if (response.files) {
                            updateCustomerPhotoLinks(response.files);
                        } else {
                            hideAllCustomerPhotoLinks();
                        }
                    } else {
                        $('#unit-detail-state').text('Rincian gagal dimuat. Tutup modal dan klik unit untuk mencoba kembali.').show();
                    }
                },
                error: function(xhr, status, error) {
                    if (status !== 'abort') $('#unit-detail-state').text('Rincian gagal dimuat. Tutup modal dan klik unit untuk mencoba kembali.').show();
                },
            });
        });

        function updateCustomerPhotoLinks(files) {
            $('.view-photo-link').hide();
            $('[id^="noFoto"]').hide();

            if (files.ktp) {
                $('#fotoKtpLink').attr('href', files.ktp).show();
            } else {
                $('#noFotoKtp').show();
            }

            if (files.pemohon) {
                $('#fotoPemohonLink').attr('href', files.pemohon).show();
            } else {
                $('#noFotoPemohon').show();
            }

            if (files.kk) {
                $('#fotoKkLink').attr('href', files.kk).show();
            } else {
                $('#noFotoKk').show();
            }

            if (files.ktp_pasangan) {
                $('#fotoKtpPasanganLink').attr('href', files.ktp_pasangan).show();
            } else {
                $('#noFotoKtpPasangan').show();
            }

            if (files.npwp) {
                $('#fotoNpwpLink').attr('href', files.npwp).show();
            } else {
                $('#noFotoNpwp').show();
            }

            if (files.bpjs) {
                $('#fotoBpjsLink').attr('href', files.bpjs).show();
            } else {
                $('#noFotoBpjs').show();
            }
        }

        function hideAllCustomerPhotoLinks() {
            $('.view-photo-link').hide();
            $('[id^="noFoto"]').show();
        }

        $('#modalDetail').on('hidden.bs.modal', function() {
            if (detailRequest) detailRequest.abort();
            $('#btn-cetak').prop('disabled', true);
        });

        function loadTagihan(id_kavling) {
            $.ajax({
                url: '{{ url('/admin/st-penjualan/tagihan') }}/' + id_kavling,
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        let htmlTagihan = '',
                            htmlPembayaran = '',
                            htmlPengeluaran = '';
                        let totalTagihan = 0,
                            totalPembayaran = 0,
                            totalPengeluaran = 0;

                        if (res.tagihan && res.tagihan.length) {
                            res.tagihan.forEach((item, i) => {
                                const jumlah = Number(item.jumlah ?? item.jumlah_tagihan ?? 0);
                                totalTagihan += jumlah;

                                htmlTagihan += `
                                <tr>
                                    <td>${i + 1}</td>
                                    <td>${item.jenis ?? item.jenis_tagihan ?? '-'}</td>
                                    <td>Rp ${numberFormat(jumlah)}</td>
                                    <td>${item.catatan ?? ''}</td>
                                </tr>
                            `;
                            });
                        } else {
                            htmlTagihan =
                                '<tr><td colspan="4" class="text-center">Tidak ada tagihan.</td></tr>';
                        }

                        if (res.pembayaran && res.pembayaran.length) {
                            res.pembayaran.forEach((item, i) => {
                                const jumlah = Number(item.jumlah ?? 0);
                                totalPembayaran += jumlah;

                                htmlPembayaran += `
                                <tr>
                                    <td>${i + 1}</td>
                                    <td>${formatTanggalIndo(item.tanggal)}</td>
                                    <td>${item.jenis_pembayaran?.nama_jenis ?? '-'}</td>
                                    <td>Rp ${numberFormat(jumlah)}</td>
                                    <td>${item.keterangan ?? '-'}</td>
                                </tr>
                            `;
                            });
                        } else {
                            htmlPembayaran =
                                '<tr><td colspan="5" class="text-center">Tidak ada pembayaran.</td></tr>';
                        }

                        if (res.pengeluaran && res.pengeluaran.length) {
                            res.pengeluaran.forEach((item, i) => {
                                const jumlah = Number(item.jumlah ?? 0);
                                totalPengeluaran += jumlah;

                                htmlPengeluaran += `
                                <tr>
                                    <td>${i + 1}</td>
                                    <td>${formatTanggalIndo(item.tanggal)}</td>
                                    <td>${item.jenis_pembayaran?.nama_jenis ?? '-'}</td>
                                    <td>Rp ${numberFormat(jumlah)}</td>
                                    <td>${item.keterangan ?? '-'}</td>
                                </tr>
                            `;
                            });
                        } else {
                            htmlPengeluaran =
                                '<tr><td colspan="5" class="text-center">Tidak ada pengeluaran.</td></tr>';
                        }

                        const sisaBayar = totalTagihan - totalPembayaran;
                        $('#sisa-bayar').text(`Rp ${numberFormat(sisaBayar)}`);

                        $('#tbody-tagihan').html(htmlTagihan);
                        $('#total-tagihan').text('Rp ' + numberFormat(totalTagihan));

                        $('#tbody-pembayaran').html(htmlPembayaran);
                        $('#total-pembayaran').text('Rp ' + numberFormat(totalPembayaran));

                        $('#tbody-pengeluaran').html(htmlPengeluaran);
                        $('#total-pengeluaran').text('Rp ' + numberFormat(totalPengeluaran));

                    } else {
                        $('#tagihan-pembayaran-wrapper').html(
                            `<div class="alert alert-danger">${res.message ?? 'Gagal mengambil data tagihan'}</div>`
                        );
                    }
                },
                error: function(xhr, status, error) {
                    $('#tagihan-pembayaran-wrapper').html(
                        '<div class="alert alert-danger">Gagal mengambil data</div>'
                    );
                }
            });
        }

        function numberFormat(x) {
            if (!x) return 0;
            return Number(x).toLocaleString('id-ID');
        }

        function formatTanggalIndo(tanggal) {
            if (!tanggal) return '-';

            const bulanIndo = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            const d = new Date(tanggal);
            const tgl = String(d.getDate()).padStart(2, '0');
            const bln = bulanIndo[d.getMonth()];
            const thn = d.getFullYear();

            return `${tgl}  ${bln}  ${thn}`;
        }
    </script>
    <script src="{{ asset('assets/svg_1.js') }}?v={{ filemtime(public_path('assets/svg_1.js')) }}"></script>
@endpush
