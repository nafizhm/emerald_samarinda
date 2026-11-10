$(function () {
    const form = document.getElementById('formData');
    if (!form || !form.dataset.url) return;

    form.noValidate = true;
    const submit = document.getElementById('submitBtn');
    const error = document.getElementById('customer-edit-errors');
    let saving = false;

    function clearErrors() {
        error.hidden = true;
        error.replaceChildren();
        form.querySelectorAll('.is-invalid').forEach((field) => {
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach((item) => item.remove());
    }

    function showErrors(result) {
        if (!result.errors) {
            error.hidden = false;
            error.textContent = result.message || 'Perubahan belum tersimpan.';
            error.focus();
            error.scrollIntoView({ behavior: 'auto', block: 'center' });
            return;
        }

        let firstField = null;
        Object.entries(result.errors).forEach(([name, messages]) => {
            const field = form.elements.namedItem(name.split('.')[0]);
            if (!field || !field.classList) return;

            field.classList.add('is-invalid');
            field.setAttribute('aria-invalid', 'true');
            field.parentElement.querySelectorAll('.invalid-feedback').forEach((item) => item.remove());
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block';
            feedback.textContent = messages[0];
            field.after(feedback);
            firstField ??= field;
        });

        if (firstField) {
            firstField.focus({ preventScroll: true });
            firstField.scrollIntoView({ behavior: 'auto', block: 'center' });
        }
    }

    $(form).on('submit', function (event) {
        event.preventDefault();
        if (saving) return;

        saving = true;
        clearErrors();
        submit.disabled = true;
        submit.querySelector('.button-text').textContent = 'Menyimpan...';

        $.ajax({
            url: form.dataset.url,
            method: 'POST',
            data: new FormData(form),
            contentType: false,
            processData: false,
            success(result) {
                if (!result.success) {
                    showErrors({ message: result.message || 'Perubahan belum tersimpan.' });
                    return;
                }
                localStorage.setItem('customerSuccess', result.message || 'Data customer berhasil diperbarui.');
                window.location.href = form.dataset.returnUrl;
            },
            error(xhr) {
                showErrors(xhr.responseJSON || { message: 'Perubahan belum tersimpan. Periksa koneksi dan coba kembali.' });
            },
            complete() {
                saving = false;
                submit.disabled = false;
                submit.querySelector('.button-text').textContent = 'Simpan perubahan';
            },
        });
    });
});
