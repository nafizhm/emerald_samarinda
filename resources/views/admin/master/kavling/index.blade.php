@extends('admin.layout')
@section('page_title', 'Data Kavling')
@push('styles')
<link rel="stylesheet" href="{{ asset('template/assets/css/kavling-custom.css') }}?v={{ filemtime(public_path('template/assets/css/kavling-custom.css')) }}">
@endpush
@section('content')
<div class="dashboard-page kavling-page">
    <div class="dashboard-intro">
        <div><p class="dashboard-eyebrow">Master data</p><h1>Data kavling</h1><p>Kelola ukuran, harga, dan keterangan kavling pada setiap proyek.</p></div>
        <div class="kavling-exports">
            <a id="export-pdf" href="{{ route('kavling.cetakPdf', 0) }}" target="_blank" rel="noopener" class="dashboard-action"><i class="bi bi-file-earmark-pdf"></i> Cetak PDF</a>
            <a id="export-excel" href="{{ route('kavling.cetakExcel', 0) }}" target="_blank" rel="noopener" class="dashboard-action"><i class="bi bi-file-earmark-excel"></i> Ekspor Excel</a>
        </div>
    </div>
    <section class="dashboard-panel">
        <div class="kavling-toolbar"><div><label for="id_lokasi">Proyek / lokasi kavling</label><select class="form-select" id="id_lokasi"><option value="">Semua proyek</option>@foreach($lokasiList as $lokasi)<option value="{{ $lokasi->id }}">{{ $lokasi->nama_kavling }}</option>@endforeach</select></div><p>Pilih proyek untuk menyaring daftar dan hasil ekspor.</p></div>
        <div class="kavling-table-wrap"><table class="table data-table align-middle"><thead><tr><th>No</th><th>Proyek</th><th>Kode kavling</th><th>Panjang</th><th>Lebar</th><th>Luas tanah</th><th>Harga jual</th><th>Keterangan</th><th>Aksi</th></tr></thead><tbody></tbody></table></div>
    </section>
</div>
<div class="modal fade" id="kavlingEditModal" tabindex="-1" aria-labelledby="kavlingEditTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content workspace-modal">
        <div class="modal-header"><div><span class="kavling-eyebrow">Edit data kavling</span><h5 class="modal-title" id="kavlingEditTitle">Edit kavling</h5><p id="edit-project-name"></p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <div id="edit-loading" role="status">Memuat data kavling...</div>
            <div id="edit-error" class="alert alert-danger" role="alert" hidden></div>
            <form id="kavlingEditForm" hidden>@csrf
                <div class="kavling-form-grid"><div class="kavling-field"><label for="edit_panjang">Panjang tanah (m)</label><input type="number" name="panjang" id="edit_panjang" class="form-control" step="0.1" max="9999999999.9" min="0" required></div>
<div class="kavling-field"><label for="edit_lebar">Lebar tanah (m)</label><input type="number" name="lebar" id="edit_lebar" class="form-control" step="0.1" max="9999999999.9" min="0" required></div>
<div class="kavling-field"><label for="edit_luas_tanah">Luas tanah (m&sup2;)</label><input type="number" name="luas_tanah" id="edit_luas_tanah" class="form-control" step="1" min="0" required></div>
<div class="kavling-field"><label for="edit_hrg_jual">Harga jual (Rp)</label><input type="text" name="hrg_jual" id="edit_hrg_jual" class="form-control format-number" inputmode="numeric" required></div>
<div class="kavling-field kavling-field-wide"><label for="edit_keterangan">Keterangan</label><textarea name="keterangan" id="edit_keterangan" rows="3" maxlength="5000" class="form-control"></textarea></div></div>
                <div class="kavling-bulk-option"><div class="form-check"><input class="form-check-input" type="checkbox" name="update_project" value="1" id="update_project"><label class="form-check-label" for="update_project">Update semua kavling dalam proyek yang sama</label></div><p id="bulk-description"></p><p>Jika dicentang, semua nilai di form ini, termasuk panjang, lebar, luas tanah, harga jual, dan keterangan, diterapkan ke seluruh kavling dalam proyek tersebut.</p></div>
            </form>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" form="kavlingEditForm" class="btn btn-primary" id="save-kavling" disabled>Simpan perubahan</button></div>
    </div></div>
</div>
@endsection
@push('scripts')
<script>
$(function() {
    const modalElement = document.getElementById('kavlingEditModal');
    const modal = new bootstrap.Modal(modalElement);
    let editId = null, editRequest = null, saving = false;
    const table = $('.data-table').DataTable({
        processing: true, serverSide: true, ordering: false, responsive: false, pageLength: 10,
        language: {search: 'Cari kavling:', lengthMenu: 'Tampilkan _MENU_', info: '_START_ - _END_ dari _TOTAL_ kavling', infoEmpty: 'Belum ada kavling', infoFiltered: '(dari _MAX_ kavling)', zeroRecords: 'Kavling tidak ditemukan', emptyTable: 'Belum ada data kavling', processing: 'Memuat data...', paginate: {previous: 'Sebelumnya', next: 'Berikutnya'}},
        ajax: {url: "{{ route('kavling.index') }}", data: function(d) {d.id_lokasi = $('#id_lokasi').val();}},
        columns: [
            {data: 'DT_RowIndex', searchable: false},
            {data: 'lokasi.nama_kavling', name: 'lokasi.nama_kavling', defaultContent: '-', render: $.fn.dataTable.render.text()},
            {data: 'kode_kavling', render: $.fn.dataTable.render.text()},
            {data: 'panjang', searchable: false}, {data: 'lebar', searchable: false}, {data: 'luas', searchable: false},
            {data: 'harga', searchable: false}, {data: 'keterangan', defaultContent: '-', render: $.fn.dataTable.render.text()},
            {data: 'action', searchable: false}
        ]
    });
    $('#id_lokasi').on('change', function() {
        table.ajax.reload();
        $('#export-pdf').attr('href', "{{ route('kavling.cetakPdf', ':id') }}".replace(':id', this.value || 0));
        $('#export-excel').attr('href', "{{ route('kavling.cetakExcel', ':id') }}".replace(':id', this.value || 0));
    });
    function openEdit(url) {
        if (editRequest) editRequest.abort();
        editId = null;
        document.getElementById('kavlingEditForm').reset();
        $('#kavlingEditForm').prop('hidden', true).find('.is-invalid').removeClass('is-invalid');
        $('.kavling-field .invalid-feedback').remove();
        $('#edit-error').prop('hidden', true);
        $('#edit-loading').show(); $('#save-kavling').prop('disabled', true);
        $('#kavlingEditTitle').text('Edit kavling'); $('#edit-project-name').text('');
        modal.show();
        editRequest = $.ajax({url: url, dataType: 'json', success: function(res) {
            const data = res.data;
            editId = data.id;
            $('#kavlingEditTitle').text('Kavling ' + data.kode_kavling);
            $('#edit-project-name').text(data.lokasi ? data.lokasi.nama_kavling : 'Tanpa proyek');
            $('#kavlingEditForm [name]').each(function() {
                if (Object.prototype.hasOwnProperty.call(data, this.name)) this.value = this.name.startsWith('hrg_') ? Number(data[this.name] || 0).toLocaleString('id-ID') : (data[this.name] ?? '');
            });
            $('#bulk-description').text((data.lokasi ? data.lokasi.nama_kavling : 'Tanpa proyek') + ' / ' + res.project_count + ' kavling');
            $('#update_project').prop('disabled', !data.id_lokasi);
            $('#edit-loading').hide(); $('#kavlingEditForm').prop('hidden', false); $('#save-kavling').prop('disabled', false);
        }, error: function(xhr, status) {
            if (status !== 'abort') {$('#edit-loading').hide(); $('#edit-error').text('Data tidak dapat dimuat. Tutup modal dan coba kembali.').prop('hidden', false);}
        }});
    }
    $(document).on('click', '.edit-kavling', function(e) {e.preventDefault(); openEdit(this.href);});
    modalElement.addEventListener('hide.bs.modal', function(e) {if (saving) e.preventDefault();});
    modalElement.addEventListener('hidden.bs.modal', function() {if (editRequest) editRequest.abort(); editId = null;});
    $('#kavlingEditForm').on('submit', function(e) {
        e.preventDefault();
        if (!editId || saving) return;
        saving = true;
        $('#save-kavling').prop('disabled', true).text('Menyimpan...');
        $('#edit-error').prop('hidden', true);
        $(this).find('.is-invalid').removeClass('is-invalid'); $('.kavling-field .invalid-feedback').remove();
        const data = new FormData(this); data.append('_method', 'PUT');
        $.ajax({url: "{{ route('kavling.update', ':id') }}".replace(':id', editId), method: 'POST', data: data, contentType: false, processData: false,
            success: function(res) {
                saving = false;
                if (!res.success) {$('#edit-error').text(res.message).prop('hidden', false); return;}
                modal.hide(); table.ajax.reload(null, false); toastr.success(res.message);
            }, error: function(xhr) {
                const res = xhr.responseJSON || {};
                $('#edit-error').text(res.message || 'Data gagal disimpan. Silakan coba kembali.').prop('hidden', false);
                Object.entries(res.errors || {}).forEach(function([key, messages]) {
                    const input = $('#edit_' + key).addClass('is-invalid');
                    $('<div class="invalid-feedback"></div>').text(messages[0]).insertAfter(input);
                });
            }, complete: function() {saving = false; $('#save-kavling').prop('disabled', false).text('Simpan perubahan');}
        });
    });
    const initialEdit = @json(request('edit'));
    if (initialEdit && @json((bool) $permissions['edit'])) openEdit("{{ route('kavling.edit', ':id') }}".replace(':id', encodeURIComponent(initialEdit)));
});
</script>
@endpush
