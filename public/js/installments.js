document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('installment-page'); if (!page) return;
    const dialog = document.getElementById('installment-modal'); const modal = new bootstrap.Modal(dialog);
    const form = document.getElementById('installment-form'); const save = document.getElementById('payment-save');
    const remaining = Number(page.dataset.remaining); let submitting = false;
    const currency = value => 'Rp ' + Number(value).toLocaleString('id-ID');
    function preview(amount) {document.getElementById('payment-total').textContent = currency(amount); document.getElementById('payment-after').textContent = 'Sisa setelah pembayaran: ' + currency(Math.max(0, remaining - amount));}
    function open(mode, amount, month, label) {
        form.reset(); document.getElementById('payment-error').hidden = true;
        document.getElementById('payment-mode').value = mode; document.getElementById('payment-month').value = month ?? '';
        document.getElementById('manual-amount-group').hidden = mode !== 'manual';
        document.getElementById('payment-amount').disabled = mode !== 'manual'; document.getElementById('payment-amount').required = mode === 'manual';
        document.getElementById('payment-amount').value = mode === 'manual' && amount > 0 ? amount : '';
        document.getElementById('payment-selection').textContent = label; preview(amount); modal.show();
        page.querySelectorAll('tbody tr[data-month]').forEach(row => row.classList.toggle('installment-selected', mode === 'through' && row.dataset.unpaid === '1' && Number(row.dataset.month) <= Number(month)));
    }
    page.querySelectorAll('.select-installment').forEach(button => button.addEventListener('click', () => open('through', Number(button.dataset.amount), Number(button.dataset.month), button.dataset.label)));
    document.getElementById('pay-custom')?.addEventListener('click', () => open('manual', 0, null, 'Pembayaran sesuai nominal dana yang diterima.'));
    document.getElementById('payment-amount').addEventListener('input', event => preview(Number(event.target.value || 0)));
    page.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', function () {
        page.querySelectorAll('[data-filter]').forEach(item => {item.classList.toggle('active', item === button); item.setAttribute('aria-pressed', item === button ? 'true' : 'false');});
        page.querySelectorAll('.installment-schedule tbody tr[data-month]').forEach(row => {row.hidden = button.dataset.filter === 'unpaid' && row.dataset.unpaid !== '1';});
    }));
    dialog.addEventListener('hide.bs.modal', event => {if (submitting) event.preventDefault();});
    dialog.addEventListener('hidden.bs.modal', () => page.querySelectorAll('.installment-selected').forEach(row => row.classList.remove('installment-selected')));
    page.querySelectorAll('.cancel-installment').forEach(button => button.addEventListener('click', async function () {
        const decision = await Swal.fire({icon:'warning', title:'Batalkan pembayaran?', text:currency(button.dataset.amount) + ' akan dikembalikan ke sisa pembayaran.', showCancelButton:true, confirmButtonText:'Ya, batalkan transaksi', cancelButtonText:'Kembali'});
        if (!decision.isConfirmed) return;
        button.disabled = true;
        try {
            const response = await fetch(button.dataset.url, {method:'DELETE', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':form.querySelector('[name="_token"]').value}, body:JSON.stringify({expected_paid:Number(page.dataset.paid)})});
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Pembatalan gagal.');
            window.location.reload();
        } catch (failure) {button.disabled=false; Swal.fire({icon:'error',title:'Pembayaran belum dibatalkan',text:failure.message});}
    }));
    form.addEventListener('submit', async function (event) {
        event.preventDefault(); if (submitting) return;
        submitting = true; save.disabled = true; save.textContent = 'Menyimpan...';
        const error = document.getElementById('payment-error'); error.hidden = true;
        try {
            const response = await fetch(page.dataset.url, {method: 'POST', headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value}, body: new FormData(form)});
            const result = await response.json();
            if (!response.ok || !result.success) {
                const messages = Object.values(result.errors || {}).flat();
                throw new Error(messages.join(' ') || result.message || 'Pembayaran gagal dicatat.');
            }
            submitting = false;
            modal.hide();
            const message = document.createElement('div');
            const text = document.createElement('p');
            text.textContent = 'Pembayaran berhasil dicatat. Kwitansi siap dicetak.';
            message.appendChild(text);
            if (result.receipt_url) {
                const receipt = document.createElement('a');
                receipt.href = result.receipt_url;
                receipt.target = '_blank';
                receipt.rel = 'noopener noreferrer';
                receipt.className = 'btn btn-primary';
                receipt.textContent = 'Cetak kwitansi';
                message.appendChild(receipt);
            }
            await Swal.fire({icon:'success', title:'Pembayaran tersimpan', html:message, confirmButtonText:'Kembali ke cicilan', allowOutsideClick:false});
            window.location.reload();
        } catch (failure) {error.textContent = failure.message; error.hidden = false; submitting = false; save.disabled = false; save.textContent = 'Simpan pembayaran';}
    });
});
