document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('installment-page');
    if (!page) return;

    const filterKavling = document.getElementById('filter-kavling');
    if (filterKavling) {
        filterKavling.addEventListener('change', function () {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('transaksi_kavling_id', this.value);
            window.location.href = currentUrl.toString();
        });
    }

    const dialog = document.getElementById('installment-modal');
    const modal = dialog ? new bootstrap.Modal(dialog) : null;
    const form = document.getElementById('installment-form');
    const save = document.getElementById('payment-save');
    const transactionDialog = document.getElementById('transaction-modal');
    const transactionModal = transactionDialog ? new bootstrap.Modal(transactionDialog) : null;
    const transactionForm = document.getElementById('transaction-form');
    const transactionSave = document.getElementById('transaction-save');
    const remaining = Number(page.dataset.remaining || 0);
    let submitting = false;
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
    document.getElementById('payment-amount').addEventListener('input', event => preview(Number(event.target.value || 0)));
    function resetTransactionErrors() {
        transactionForm.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
        transactionForm.querySelectorAll('.invalid-feedback').forEach(message => message.remove());
    }
    function showTransactionErrors(errors) {
        Object.entries(errors).forEach(([key, messages]) => {
            const input = transactionForm.querySelector('#' + key); if (!input) return;
            input.classList.add('is-invalid');
            const message = document.createElement('div'); message.className = 'invalid-feedback';
            message.textContent = messages[0]; input.insertAdjacentElement('afterend', message);
        });
    }
    document.getElementById('add-transaction')?.addEventListener('click', () => {
        transactionForm.reset(); resetTransactionErrors();
        const tkInput = document.getElementById('form-transaksi-kavling-id');
        if (tkInput && page.dataset.transaksiKavlingId) {
            tkInput.value = page.dataset.transaksiKavlingId;
        }
        transactionForm.dataset.url = transactionForm.dataset.createUrl || transactionForm.dataset.url;
        transactionForm.dataset.method = 'POST'; document.getElementById('transaction-modal-title').textContent = 'Tambah Transaksi';
        document.getElementById('transaction-preview').innerHTML = '<span style="color: #6c757d;">Tidak ada File</span>';
        transactionModal.show();
    });
    document.addEventListener('click', async function (event) {
        const button = event.target.closest('.edit-transaction');
        if (!button) return;
        event.preventDefault();
        resetTransactionErrors();
        try {
            const response = await fetch(button.dataset.url, {headers:{'Accept':'application/json'}});
            const result = await response.json();
            if (!response.ok || !result.success) {
                Swal.fire({icon:'error', title:'Transaksi tidak dapat dibuka'});
                return;
            }
            const data = result.data;
            transactionForm.reset();
            const tkInput = document.getElementById('form-transaksi-kavling-id');
            if (tkInput && page.dataset.transaksiKavlingId) {
                tkInput.value = page.dataset.transaksiKavlingId;
            }
            transactionForm.dataset.url = button.dataset.updateUrl;
            transactionForm.dataset.method = 'PUT';
            document.getElementById('transaction-modal-title').textContent = 'Edit Transaksi';
            document.getElementById('tanggal').value = data.tanggal;
            const kategori = document.getElementById('id_kategori_transaksi');
            kategori.value = String(data.id_kategori_transaksi);
            if (window.jQuery && window.jQuery(kategori).hasClass('select2-hidden-accessible')) {
                window.jQuery(kategori).trigger('change');
            } else {
                kategori.dispatchEvent(new Event('change', {bubbles:true}));
            }
            document.getElementById('nominal').value = Number(data.nominal).toLocaleString('id-ID');
            document.getElementById('keterangan').value = data.keterangan || '';
            const preview = document.getElementById('transaction-preview');
            preview.replaceChildren();
            if (data.lampiran) {
                const image = document.createElement('img');
                image.src = '/assets/keuangan/pemasukan/' + encodeURIComponent(data.lampiran);
                image.alt = 'Lampiran transaksi';
                image.style.maxHeight = '100%';
                image.style.maxWidth = '100%';
                preview.appendChild(image);
            } else {
                preview.innerHTML = '<span style="color: #6c757d;">Tidak ada File</span>';
            }
            transactionModal.show();
        } catch (err) {
            Swal.fire({icon:'error', title:'Gagal memuat transaksi', text:err.message});
        }
    });
    document.getElementById('lampiran').addEventListener('change', function () {
        const previewBox = document.getElementById('transaction-preview'); const file = this.files[0];
        previewBox.replaceChildren();
        if (!file) {previewBox.innerHTML = '<span style="color: #6c757d;">Tidak ada File</span>'; return;}
        if (file.type.startsWith('image/')) {const image = document.createElement('img'); image.src = URL.createObjectURL(file); image.alt = 'Pratinjau lampiran'; image.style.maxHeight = '100%'; image.style.maxWidth = '100%'; previewBox.appendChild(image); return;}
        const name = document.createElement('span'); name.className = 'text-muted text-center px-2'; name.textContent = file.name; previewBox.appendChild(name);
    });
    document.getElementById('nominal').addEventListener('input', function () {
        const angka = this.value.replace(/\D/g, '');
        this.value = angka ? Number(angka).toLocaleString('id-ID') : '';
    });
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
            const response = await fetch(button.dataset.url, {method:'DELETE', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':form.querySelector('[name="_token"]').value}, body:JSON.stringify({expected_paid:Number(page.dataset.paid), id_transaksi_kavling:Number(page.dataset.transaksiKavlingId)})});
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Pembatalan gagal.');
            window.location.reload();
        } catch (failure) {button.disabled=false; Swal.fire({icon:'error',title:'Pembayaran belum dibatalkan',text:failure.message});}
    }));
    document.addEventListener('click', async function (event) {
        const button = event.target.closest('.delete-transaction');
        if (!button) return;
        event.preventDefault();
        const decision = await Swal.fire({
            icon: 'warning',
            title: 'Hapus transaksi?',
            text: currency(button.dataset.amount) + ' akan dikembalikan ke sisa pembayaran.',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        });
        if (!decision.isConfirmed) return;
        button.disabled = true;
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || transactionForm?.querySelector('[name="_token"]')?.value
                || form?.querySelector('[name="_token"]')?.value;
            const response = await fetch(button.dataset.url, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Transaksi gagal dihapus.');
            await Swal.fire({icon: 'success', title: 'Transaksi dihapus'});
            window.location.reload();
        } catch (failure) {
            button.disabled = false;
            Swal.fire({icon: 'error', title: 'Transaksi belum dihapus', text: failure.message});
        }
    });
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
    transactionForm.addEventListener('submit', async function (event) {
        event.preventDefault(); transactionSave.disabled = true; resetTransactionErrors();
        const spinner = transactionSave.querySelector('.spinner-border'); const buttonText = transactionSave.querySelector('.button-text');
        spinner.classList.remove('d-none'); buttonText.textContent = 'Menyimpan...';
        try {
            const formData = new FormData(transactionForm); if (transactionForm.dataset.method === 'PUT') formData.append('_method', 'PUT');
            const response = await fetch(transactionForm.dataset.url, {method:'POST', headers:{'Accept':'application/json', 'X-CSRF-TOKEN': transactionForm.querySelector('[name="_token"]').value}, body:formData});
            const result = await response.json();
            if (!response.ok || !result.success) {
                if (response.status === 422 && result.errors) {showTransactionErrors(result.errors); toastr.error('Ada inputan yang salah!', 'GAGAL!', {progressBar:true, timeOut:3500, positionClass:'toast-bottom-right'});}
                throw new Error(result.message || 'Transaksi gagal dicatat.');
            }
            transactionModal.hide();
            await Swal.fire({icon:'success', title:'Transaksi tersimpan', text:'Riwayat, piutang, dan tahap pembayaran telah diperbarui.'});
            window.location.reload();
        } catch (failure) {transactionSave.disabled = false; spinner.classList.add('d-none'); buttonText.textContent = 'Simpan';}
    });
});
