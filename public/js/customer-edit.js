$(function () {
    const form=document.getElementById('formData');if(!form || !form.dataset.url)return;
    const submit=document.getElementById('submitBtn');const error=document.getElementById('customer-edit-errors');let saving=false;
    function clearErrors(){error.hidden=true;error.replaceChildren();form.querySelectorAll('.is-invalid').forEach(field=>{field.classList.remove('is-invalid');field.removeAttribute('aria-invalid');});form.querySelectorAll('.invalid-feedback').forEach(item=>item.remove());}
    $(form).on('submit',function(event){
        event.preventDefault();if(saving)return;saving=true;clearErrors();submit.disabled=true;submit.querySelector('.button-text').textContent='Menyimpan...';
        $.ajax({url:form.dataset.url,method:'POST',data:new FormData(form),contentType:false,processData:false,
            success:function(result){if(!result.success){showErrors({message:result.message || 'Perubahan belum tersimpan.'});return;}localStorage.setItem('customerSuccess',result.message || 'Data pelanggan berhasil diperbarui.');window.location.href=form.dataset.returnUrl;},
            error:function(xhr){showErrors(xhr.responseJSON || {message:'Perubahan belum tersimpan. Periksa koneksi dan coba kembali.'});},
            complete:function(){saving=false;submit.disabled=false;submit.querySelector('.button-text').textContent='Simpan perubahan';}
        });
    });
    function showErrors(result){
        error.hidden=false;const title=document.createElement('strong');title.textContent=result.errors ? 'Periksa isian berikut:' : (result.message || 'Perubahan belum tersimpan.');error.append(title);
        if(result.errors){const list=document.createElement('ul');Object.entries(result.errors).forEach(([name,messages])=>{const field=form.elements.namedItem(name);const item=document.createElement('li');item.textContent=messages[0];list.append(item);if(field && field.classList){field.classList.add('is-invalid');field.setAttribute('aria-invalid','true');const feedback=document.createElement('div');feedback.className='invalid-feedback';feedback.textContent=messages[0];field.after(feedback);}});error.append(list);}
        error.focus();error.scrollIntoView({behavior:'auto',block:'center'});
    }
});
