document.addEventListener('DOMContentLoaded', function () {
    const trigger = document.getElementById('due-floating');
    if (!trigger) return;
    const popup = document.getElementById('due-popup');
    const list = document.getElementById('due-popup-list');
    const badge = document.getElementById('due-floating-badge');
    let refreshing = false;
    function closePopup(focus) { popup.hidden = true; trigger.setAttribute('aria-expanded', 'false'); if (focus) trigger.focus(); }
    function state(message) {const p = document.createElement('p'); p.className = 'due-popup-state'; p.textContent = message; list.replaceChildren(p);}
    function render(data) {
        badge.textContent = data.count > 99 ? '99+' : data.count;
        badge.classList.toggle('is-empty', data.count === 0);
        trigger.title = data.count + ' pelanggan jatuh tempo';
        trigger.setAttribute('aria-label', data.count + ' pelanggan jatuh tempo. Buka popup pengingat.');
        document.getElementById('due-popup-summary').textContent = Math.min(10, data.count) + ' dari ' + data.count + ' pelanggan / jatuh tempo terlama';
        list.replaceChildren();
        if (!data.items.length) {state('Tidak ada cicilan jatuh tempo yang belum dibayar.'); return;}
        data.items.slice(0, 10).forEach(function (item) {
            const link = document.createElement('a'); link.className = 'due-popup-item'; link.href = item.detail_url;
            const name = document.createElement('strong'); name.textContent = item.name;
            const unit = document.createElement('span'); unit.textContent = item.codes + ' / ' + item.locations;
            const meta = document.createElement('div'); meta.className = 'due-popup-meta';
            const status = document.createElement('span'); status.textContent = item.late_days ? 'Telat ' + item.late_days + ' hari' : 'Hari ini';
            status.className = item.late_days ? 'late' : 'today';
            const amount = document.createElement('b'); amount.textContent = 'Rp ' + Number(item.due_amount).toLocaleString('id-ID');
            const date = document.createElement('small'); date.textContent = 'Jatuh tempo ' + item.due_date;
            meta.append(status, amount); link.append(name, unit, date, meta); list.append(link);
        });
    }
    async function refresh() {
        if (document.hidden || refreshing) return;
        refreshing = true;
        try {
            const response = await fetch(trigger.dataset.notificationUrl, {headers: {'Accept': 'application/json'}, cache: 'no-store'});
            if (!response.ok) throw new Error('Tidak dapat memuat pengingat');
            render(await response.json());
        } catch (_) {if (!popup.hidden) state('Pengingat gagal dimuat. Tutup dan buka kembali untuk mencoba lagi.');}
        finally {refreshing = false;}
    }
    trigger.addEventListener('click', function () {
        if (!popup.hidden) {closePopup(false); return;}
        popup.hidden = false; trigger.setAttribute('aria-expanded', 'true');
        state('Memuat pelanggan...'); refresh(); document.getElementById('due-popup-close').focus();
    });
    document.getElementById('due-popup-close').addEventListener('click', function () {closePopup(true);});
    document.addEventListener('click', function (event) {if (!document.getElementById('due-notification').contains(event.target)) closePopup(false);});
    document.addEventListener('keydown', function (event) {if (event.key === 'Escape' && !popup.hidden) closePopup(true);});
    setInterval(refresh, 90000); document.addEventListener('visibilitychange', refresh); window.addEventListener('focus', refresh);
});
