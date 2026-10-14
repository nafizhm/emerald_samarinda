(function () {
    window.showSuccessAlert = function (message) {
        return window.Swal.fire({
            icon: 'success', title: 'Berhasil!',
            text: message || 'Data berhasil disimpan.',
            confirmButtonText: 'Oke', buttonsStyling: false,
            customClass: { popup: 'workspace-success-alert', confirmButton: 'btn btn-primary' }
        });
    };
    if (window.toastr && window.Swal) {
        window.toastr.success = function (message) { return window.showSuccessAlert(message); };
    }
    if (window.jQuery?.fn.dataTable) {
        window.jQuery.extend(true, window.jQuery.fn.dataTable.defaults, {
            language: {search:'Cari data:', searchPlaceholder:'Ketik kata pencarian...', lengthMenu:'Tampilkan _MENU_', info:'_START_ - _END_ dari _TOTAL_ data', infoEmpty:'Belum ada data', infoFiltered:'(dari _MAX_ data)', emptyTable:'Belum ada data yang ditampilkan.', zeroRecords:'Tidak ada hasil yang cocok. Coba kata pencarian lain.', processing:'Memuat data...', loadingRecords:'Memuat data...', paginate:{first:'Pertama',last:'Terakhir',next:'Berikutnya',previous:'Sebelumnya'}}
        });
    }
    function requiredHints(root) {
        root.querySelectorAll('form').forEach(function (form) {
            form.querySelectorAll('input[required],select[required],textarea[required]').forEach(function (field) {
                if (field.type === 'hidden' || field.disabled || !field.id) return;
                const label = Array.from(form.querySelectorAll('label')).find(item => item.htmlFor === field.id);
                if (label && !label.textContent.includes('*') && !label.querySelector('.workspace-required')) {
                    const mark = document.createElement('span'); mark.className='workspace-required'; mark.textContent=' *'; mark.setAttribute('aria-hidden','true'); label.append(mark);
                }
                field.setAttribute('aria-required','true');
            });
        });
    }
    function enhanceSearch(root) {
        root.querySelectorAll('.dataTables_filter').forEach(function (filter) {
            const input=filter.querySelector('input'); if (!input || filter.querySelector('.workspace-clear-search')) return;
            if (!input.getAttribute('placeholder')) input.placeholder='Ketik kata pencarian...';
            const clear=document.createElement('button'); clear.type='button'; clear.className='workspace-clear-search'; clear.textContent='Hapus pencarian'; clear.hidden=!input.value;
            clear.addEventListener('click',function () { const wrapper=filter.closest('.dataTables_wrapper');const table=wrapper?.querySelector('table');input.value='';clear.hidden=true;if(table) window.jQuery(table).DataTable().search('').draw();input.focus(); });
            input.addEventListener('input',()=>{clear.hidden=!input.value;});filter.append(clear);
        });
    }
    function menuSearch() {
        const dialog=document.getElementById('workspace-menu-search'); if (!dialog) return;
        const input=document.getElementById('workspace-menu-query');const results=document.getElementById('workspace-menu-results');const empty=document.getElementById('workspace-menu-empty');
        const seen=new Set();const entries=[];
        document.querySelectorAll('#sidebar a.sidebar-link,#sidebar a.submenu-link,#workspace-home-link').forEach(link=>{
            const href=link.getAttribute('href'); if(!href || href==='#' || seen.has(link.href))return;
            const url=new URL(link.href,window.location.href);if(url.origin!==window.location.origin)return;
            seen.add(link.href);const group=link.matches('.submenu-link') ? (link.closest('.sidebar-item')?.querySelector('.sidebar-link span')?.textContent.trim() || '') : '';
            entries.push({href:link.href,title:link.id==='workspace-home-link' ? 'Beranda' : (link.getAttribute('title') || link.textContent.trim()),group});
        });
        function render() {
            const query=input.value.trim().toLocaleLowerCase('id');results.replaceChildren();
            const matches=entries.filter(item=>(item.title+' '+item.group).toLocaleLowerCase('id').includes(query));
            matches.forEach(item=>{const link=document.createElement('a');link.href=item.href;link.className='workspace-menu-result';const text=document.createElement('div');const title=document.createElement('strong');title.textContent=item.title;const group=document.createElement('small');group.textContent=item.group || 'Menu utama';const arrow=document.createElement('i');arrow.className='bi bi-arrow-up-right';text.append(title,group);link.append(text,arrow);results.append(link);});
            empty.hidden=matches.length!==0;
        }
        input.addEventListener('input',render);
        dialog.addEventListener('shown.bs.modal',()=>{input.value='';render();input.focus();});
        dialog.addEventListener('keydown',event=>{
            const links=Array.from(results.querySelectorAll('a'));if(!links.length)return;
            if(event.key==='Enter' && event.target===input){event.preventDefault();links[0].click();}
            if(event.key==='ArrowDown' || event.key==='ArrowUp'){event.preventDefault();const index=links.indexOf(document.activeElement);const next=event.key==='ArrowDown' ? Math.min(links.length-1,index+1) : (index<0 ? links.length-1 : Math.max(0,index-1));links[next].focus();}
        });
        document.addEventListener('keydown',event=>{if((event.ctrlKey||event.metaKey) && event.key.toLowerCase()==='k'){if(document.querySelector('.modal.show:not(#workspace-menu-search)'))return;event.preventDefault();bootstrap.Modal.getOrCreateInstance(dialog).show();}});
        render();
    }
    function containTables(root) {
        root.querySelectorAll('.workspace-page table.table, .workspace-modal table.table').forEach(function (table) {
            if (table.matches('.data-table') && !table.closest('.dataTables_wrapper')) return;
            if (table.closest('.table-responsive, .workspace-table-scroll') || table.parentElement.closest('table')) return;
            const viewport = document.createElement('div');
            viewport.className = 'workspace-table-scroll';
            viewport.setAttribute('role', 'region');
            viewport.setAttribute('aria-label', 'Tabel data, geser untuk melihat kolom lainnya');
            viewport.tabIndex = 0;
            table.parentNode.insertBefore(viewport, table);
            viewport.appendChild(table);
        });
    }
    function start() {
        const flashMessage = document.getElementById('workspace-success-message');
        if (flashMessage) window.showSuccessAlert(flashMessage.textContent);
        const kavlingMessage = localStorage.getItem('kavlingSuccess');
        if (kavlingMessage) {
            localStorage.removeItem('kavlingSuccess');
            window.showSuccessAlert(kavlingMessage);
        }
        containTables(document); requiredHints(document); enhanceSearch(document); menuSearch();
        if (window.jQuery) window.jQuery(document).on('init.dt.workspace', function () { containTables(document); enhanceSearch(document); });
        document.addEventListener('shown.bs.modal', function (event) { containTables(event.target); requiredHints(event.target); });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
