$(function () {
    const page = document.getElementById('payment-list-page'); if (!page) return;
    let searchTimer;
    const currency = value => 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    function filterState() {
        const status = $('#payment-status').val();
        page.querySelectorAll('[data-status]').forEach(button => {const active = button.dataset.status === status; button.classList.toggle('active', active); button.setAttribute('aria-pressed', active ? 'true' : 'false');});
        document.getElementById('payment-reset').hidden = !status && !$('#payment-location').val() && !$('#payment-method').val() && !$('#payment-search').val();
    }
    const element = $('#payment-list-table');
    element.on('xhr.dt', function (_event, _settings, data) {
        if (!data) return;
        document.getElementById('payment-list-error').hidden = true;
        const summary = data.summary;
        if (summary) {
            ['total', 'outstanding', 'due', 'settled'].forEach(key => {document.getElementById('summary-' + key).textContent = summary[key];});
            document.getElementById('summary-balance').textContent = currency(summary.balance);
        }
    });
    const table = element.DataTable({
        processing:true, serverSide:true, ordering:false, responsive:false, pageLength:10, autoWidth:false,
        dom:'rt<"payment-pagination"lip>',
        ajax:{url:page.dataset.url, data:function (data) {data.status=$('#payment-status').val(); data.lokasi=$('#payment-location').val(); data.jenis=$('#payment-method').val();}, error:function () {document.getElementById('payment-list-error').hidden=false; element.closest('.dataTables_wrapper').find('.dataTables_processing').hide();}},
        language:{processing:'Memuat pembayaran...', lengthMenu:'Tampilkan _MENU_', info:'_START_ - _END_ dari _TOTAL_ pelanggan', infoEmpty:'Belum ada pelanggan', infoFiltered:'(dari _MAX_ pelanggan aktif)', emptyTable:'Belum ada data pembayaran pelanggan.', zeroRecords:'Tidak ada pelanggan yang cocok. Coba kata pencarian atau filter lain.', paginate:{previous:'Sebelumnya',next:'Berikutnya'}},
        columns:[{data:'customer',name:'customer'},{data:'unit',name:'unit'},{data:'method',searchable:false},{data:'payment_progress',searchable:false},{data:'balance',searchable:false},{data:'payment_status',searchable:false},{data:'action',searchable:false}],
        createdRow:function (row) {const labels=['Pelanggan','Kavling / proyek','Jenis pembelian','Progres bayar','Sisa pembayaran','Status pembayaran','']; row.querySelectorAll('td').forEach((cell,index)=>cell.setAttribute('data-label',labels[index]));},
        initComplete:function () {element.wrap('<div class="payment-table-viewport" role="region" aria-label="Daftar pembayaran pelanggan" tabindex="0"></div>');}
    });
    $('#payment-status,#payment-location,#payment-method').on('change',function () {filterState(); table.ajax.reload();});
    page.querySelectorAll('[data-status]').forEach(button => button.addEventListener('click',()=>$('#payment-status').val(button.dataset.status).trigger('change')));
    $('#payment-search').on('input',function () {clearTimeout(searchTimer); filterState(); searchTimer=setTimeout(()=>table.search(this.value.trim()).draw(),300);});
    $('#payment-reset').on('click',function () {clearTimeout(searchTimer); $('#payment-status,#payment-location,#payment-method,#payment-search').val(''); filterState(); table.search('').draw();});
    $('#payment-refresh').on('click',()=>table.ajax.reload(null,false));
    filterState();
});
