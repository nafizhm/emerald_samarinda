<style>
.media-tile {height:100%; border:1px solid var(--bs-border-color, #e2e8f0); border-radius:16px; overflow:hidden; background:var(--bs-body-bg,#fff)}
.media-picture {height:180px; display:flex; align-items:center; justify-content:center; background-color:#f1f5f9; background-image:linear-gradient(45deg,#e2e8f0 25%,transparent 25%),linear-gradient(-45deg,#e2e8f0 25%,transparent 25%),linear-gradient(45deg,transparent 75%,#e2e8f0 75%),linear-gradient(-45deg,transparent 75%,#e2e8f0 75%); background-size:20px 20px; background-position:0 0,0 10px,10px -10px,-10px 0}
.media-picture img {max-width:85%;max-height:145px;object-fit:contain}
.media-drop {position:relative;border:2px dashed #b7c5df;border-radius:14px;padding:28px;text-align:center;background:#f8faff;color:#334155;cursor:pointer}
.media-drop.dragging {border-color:#435ebe;background:#edf2ff}
.media-drop:focus-within {outline:3px solid #a6b9ff}
.media-drop input {position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
#media-selected-preview {max-height:220px;max-width:100%;object-fit:contain}
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div><h5 class="mb-1">Gambar aplikasi</h5><p class="text-muted mb-0">Pilih jenis media, lalu unggah atau ganti gambarnya. JPG, PNG, WebP · maksimal 3 MB.</p></div>
    <span class="badge bg-light-success" id="media-count">{{ $media->where('nama_file', '!=', '')->count() }} / {{ $media->count() }} terisi</span>
</div>
<div class="row g-4">
@foreach($media as $item)
    <div class="col-12 col-md-6 col-xl-3">
        <article class="media-tile" data-media-id="{{ $item->id }}" data-title="{{ ucfirst($item->jenis_data) }}" data-upload="{{ route('pengaturan-media.update', $item->id) }}" data-delete="{{ route('pengaturan-media.destroy', $item->id) }}" data-file="{{ $item->nama_file ? asset('config_media/'.$item->nama_file) : '' }}">
            <div class="media-picture">
                <img class="media-image {{ !$item->nama_file ? 'd-none' : '' }}" @if($item->nama_file) src="{{ asset('config_media/'.$item->nama_file) }}" @endif alt="{{ $item->jenis_data }}">
                <div class="media-empty text-center text-secondary {{ $item->nama_file ? 'd-none' : '' }}"><i class="bi bi-image fs-1 d-block"></i><span>Belum ada gambar</span></div>
            </div>
            <div class="p-3">
                <h6>{{ ucfirst($item->jenis_data) }}</h6>
                <p class="small text-muted" style="min-height:40px">{{ $item->keterangan }}</p>
                <p class="media-filename small text-muted text-truncate" title="{{ $item->nama_file }}">{{ $item->nama_file ?: 'Unggah gambar pertama Anda' }}</p>
                <div class="d-flex gap-2 flex-wrap">
                    @if($mediaPermissions['edit'])<button type="button" class="btn btn-primary btn-sm media-upload"><i class="bi bi-cloud-arrow-up me-1"></i><span>{{ $item->nama_file ? 'Ganti gambar' : 'Upload gambar' }}</span></button>@endif
                    <button type="button" class="btn btn-light btn-sm media-preview" @disabled(!$item->nama_file) aria-label="Lihat {{ $item->jenis_data }}" title="Lihat gambar"><i class="bi bi-eye"></i></button>
                    @if($mediaPermissions['hapus'])<button type="button" class="btn btn-light-danger btn-sm media-delete" @disabled(!$item->nama_file) aria-label="Hapus {{ $item->jenis_data }}" title="Hapus gambar"><i class="bi bi-trash"></i></button>@endif
                </div>
            </div>
        </article>
    </div>
@endforeach
</div>
<div class="modal fade" id="media-upload-modal" tabindex="-1" aria-labelledby="media-upload-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="media-upload-title">Upload gambar</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <form id="media-upload-form">
            <div class="modal-body">
                <p class="text-muted small">Gambar lama diganti setelah gambar baru berhasil disimpan.</p>
                <label class="media-drop d-block" id="media-drop-zone" for="media-file"><i class="bi bi-cloud-arrow-up fs-1 d-block mb-2"></i><strong>Klik atau tarik gambar ke sini</strong><span class="d-block small mt-1">JPG, PNG, WebP · maksimal 3 MB</span><input id="media-file" name="nama_file" type="file" accept="image/jpeg,image/png,image/webp" aria-label="Pilih gambar"></label>
                <div id="media-file-info" class="small text-muted mt-3" aria-live="polite"></div>
                <div class="text-center mt-3"><img id="media-selected-preview" class="d-none" alt="Pratinjau gambar pilihan"></div>
                <div id="media-upload-error" class="alert alert-danger mt-3 d-none" role="alert"></div>
                <div id="media-upload-progress" class="progress mt-3 d-none" aria-label="Progres upload"><div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" id="media-save" class="btn btn-primary" disabled>Simpan gambar</button></div>
        </form>
    </div></div>
</div>
<div class="modal fade" id="media-preview-modal" tabindex="-1" aria-labelledby="media-preview-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 id="media-preview-title" class="modal-title">Pratinjau gambar</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body text-center"><img id="media-preview-image" alt="Pratinjau media" style="max-width:100%;max-height:65vh"></div>
        <div class="modal-footer"><a id="media-download" class="btn btn-primary" download>Download gambar</a></div>
    </div></div>
</div>
@push('scripts')
<script>
$(function() {
    let activeCard = null, selectedFile = null, previewUrl = null, uploading = false;
    const uploadElement = document.getElementById('media-upload-modal');
    const uploadModal = new bootstrap.Modal(uploadElement);
    const previewModal = new bootstrap.Modal(document.getElementById('media-preview-modal'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    function showError(message) { $('#media-upload-error').text(message).removeClass('d-none'); }
    function chooseFile(file) {
        selectedFile = null;
        $('#media-save').prop('disabled', true);
        $('#media-selected-preview').addClass('d-none').removeAttr('src');
        $('#media-upload-error').addClass('d-none');
        $('#media-file-info').text('');
        if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
        if (!file) return;
        if (!['image/jpeg','image/png','image/webp'].includes(file.type)) return showError('Gunakan gambar JPG, PNG, atau WebP.');
        if (file.size > 3 * 1024 * 1024) return showError('Ukuran gambar maksimal 3 MB.');
        selectedFile = file;
        previewUrl = URL.createObjectURL(file);
        $('#media-selected-preview').attr('src',previewUrl).removeClass('d-none');
        $('#media-file-info').text(file.name + ' · ' + (file.size / 1024).toFixed(0) + ' KB');
        $('#media-save').prop('disabled',false);
    }
    function updateCard(card, url, name) {
        card.attr('data-file',url);
        card.find('.media-image').toggleClass('d-none',!url);
        if (url) card.find('.media-image').attr('src',url); else card.find('.media-image').removeAttr('src');
        card.find('.media-empty').toggleClass('d-none',!!url);
        card.find('.media-filename').text(name || 'Unggah gambar pertama Anda').attr('title',name || '');
        card.find('.media-upload span').text(url ? 'Ganti gambar' : 'Upload gambar');
        card.find('.media-preview,.media-delete').prop('disabled',!url);
        $('#media-count').text($('.media-tile').filter(function(){return !!$(this).attr('data-file');}).length + ' / ' + $('.media-tile').length + ' terisi');
    }
    $('.media-upload').on('click',function(){
        activeCard = $(this).closest('.media-tile');
        $('#media-upload-title').text('Upload ' + activeCard.attr('data-title'));
        $('#media-upload-form')[0].reset(); chooseFile(null);
        $('#media-upload-progress').addClass('d-none');
        uploadModal.show();
    });
    $('#media-file').on('change',function(){chooseFile(this.files[0]);});
    $('#media-drop-zone').on('dragover',function(event){event.preventDefault();$(this).addClass('dragging');})
        .on('dragleave',function(){$(this).removeClass('dragging');})
        .on('drop',function(event){event.preventDefault();$(this).removeClass('dragging');if(!uploading) chooseFile(event.originalEvent.dataTransfer.files[0]);});
    uploadElement.addEventListener('hide.bs.modal',function(event){if(uploading) event.preventDefault();});
    uploadElement.addEventListener('hidden.bs.modal',function(){chooseFile(null);});
    $('#media-upload-form').on('submit',function(event){
        event.preventDefault(); if(!selectedFile || uploading) return;
        const card = activeCard;
        const form = new FormData();form.append('nama_file',selectedFile);form.append('_method','PUT');
        uploading = true;
        $('#media-save').prop('disabled',true).text('Menyimpan...');
        $('#media-file').prop('disabled',true);
        $('#media-upload-error').addClass('d-none');$('#media-upload-progress').removeClass('d-none');
        $.ajax({url:card.attr('data-upload'),method:'POST',data:form,contentType:false,processData:false,headers:{'X-CSRF-TOKEN':csrf},
            xhr:function(){const xhr=$.ajaxSettings.xhr();xhr.upload.addEventListener('progress',function(event){if(event.lengthComputable){const percent=Math.round(event.loaded/event.total*100);$('#media-upload-progress .progress-bar').css('width',percent+'%').text(percent+'%').attr('aria-valuenow',percent);}});return xhr;},
            success:function(response){uploading=false;updateCard(card,response.url,response.name);uploadModal.hide();toastr.success(response.message);},
            error:function(xhr){showError(Object.values(xhr.responseJSON?.errors || {}).flat()[0] || xhr.responseJSON?.message || 'Upload gagal. Periksa koneksi dan coba lagi.');},
            complete:function(){uploading=false;$('#media-file').prop('disabled',false);$('#media-save').prop('disabled',!selectedFile).text('Simpan gambar');}
        });
    });
    $('.media-preview').on('click',function(){const card=$(this).closest('.media-tile');$('#media-preview-title').text(card.attr('data-title'));$('#media-preview-image').attr('src',card.attr('data-file'));$('#media-download').attr('href',card.attr('data-file'));previewModal.show();});
    $('.media-delete').on('click',function(){
        const card=$(this).closest('.media-tile'), button=$(this);
        Swal.fire({title:'Hapus gambar?',text:'Gambar '+card.attr('data-title')+' akan dihapus. Anda bisa mengunggah gambar baru setelahnya.',icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus',cancelButtonText:'Batal',confirmButtonColor:'#dc3545'}).then(function(result){
            if(!result.isConfirmed) return;
            card.find('button').prop('disabled',true);
            $.ajax({url:card.attr('data-delete'),method:'DELETE',headers:{'X-CSRF-TOKEN':csrf},success:function(response){updateCard(card,'','');toastr.success(response.message);},error:function(xhr){toastr.error(xhr.responseJSON?.message || 'Gambar gagal dihapus.');},complete:function(){card.find('button').prop('disabled',false);card.find('.media-preview,.media-delete').prop('disabled',!card.attr('data-file'));}});
        });
    });
});
</script>
@endpush
