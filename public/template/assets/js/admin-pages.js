(function () {
    if (window.jQuery?.fn.dataTable) {
        window.jQuery.extend(true, window.jQuery.fn.dataTable.defaults, {
            language: {search:'', searchPlaceholder:(window.workspaceSearchPlaceholder || 'Ketik kata pencarian...'), lengthMenu:'Tampilkan _MENU_', info:'_START_ - _END_ dari _TOTAL_ data', infoEmpty:'Belum ada data', infoFiltered:'(dari _MAX_ data)', emptyTable:'Belum ada data yang ditampilkan.', zeroRecords:'Tidak ada hasil yang cocok. Coba kata pencarian lain.', processing:'Memuat data...', loadingRecords:'Memuat data...', paginate:{first:'Pertama',last:'Terakhir',next:'Berikutnya',previous:'Sebelumnya'}}
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
            const input=filter.querySelector('input'); if (!input) return;
            input.placeholder=window.workspaceSearchPlaceholder || input.getAttribute('placeholder') || 'Ketik kata pencarian...';
            input.type='search';
            if (filter.querySelector('.workspace-search-wrap')) return;
            const label=filter.querySelector('label');
            if (label) Array.from(label.childNodes).forEach(function (node) { if (node.nodeType===3 && node.textContent.trim()!=='') node.remove(); });
            const wrap=document.createElement('div'); wrap.className='workspace-search-wrap';
            const icon=document.createElement('i'); icon.className='bi bi-search'; icon.setAttribute('aria-hidden','true');
            input.parentNode.insertBefore(wrap, input);
            wrap.append(icon, input);
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
    function enhanceSelects(root) {
        if (!window.jQuery || !window.jQuery.fn.select2) return;
        const $ = window.jQuery;
        $(root).find('select').each(function () {
            const $el = $(this);
            if ($el.hasClass('select2-hidden-accessible')) return;
            if ($el.closest('.dataTables_length').length || $el.hasClass('swal2-select') || $el.is('[data-no-select2]')) return;

            const modal = $el.closest('.modal');
            const dropdownParent = modal.length ? modal : $(document.body);
            const isMultiple = $el.prop('multiple');
            const placeholder = $el.attr('placeholder') || $el.data('placeholder') || $el.find('option[value=""]').first().text() || (isMultiple ? 'Pilih beberapa opsi...' : 'Pilih opsi...');

            $el.select2({
                width: '100%',
                placeholder: placeholder,
                allowClear: !$el.prop('required'),
                dropdownParent: dropdownParent
            });
        });
    }
    function start() {
        requiredHints(document); enhanceSearch(document); menuSearch();
        setTimeout(function () { enhanceSelects(document); }, 0);
        if (window.jQuery) window.jQuery(document).on('init.dt.workspace', function () { enhanceSearch(document); });
        document.addEventListener('shown.bs.modal', function (event) { requiredHints(event.target); enhanceSearch(event.target); enhanceSelects(event.target); });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
