@extends('admin.layout')
@section('page_title', 'Konten Halaman Publik')
@section('content')
    <style>
        #icon-picker {
            height: 300px;
            overflow-y: scroll;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            align-content: flex-start;
            padding: 4px;
        }

        .icon-option {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            margin: 0;
        }

        .icon-option:hover {
            background-color: #f0f0f0;
        }

        #icon-preview i,
        #icon-preview svg {
            font-size: 10px;
            width: 14px;
            height: 14px;
            line-height: 14px;
        }
    </style>


    <div class="content-wrapper dashboard-page workspace-page">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header workspace-page-header">
                                <div class="d-flex align-content-center justify-content-between">
                                    <div class="workspace-heading"><p class="dashboard-eyebrow">Pengaturan</p><h1>Konten Halaman Publik</h1><p class="workspace-description">Kelola konten dan bagian yang ditampilkan pada halaman publik.</p></div>
                                    <div class="d-flex align-items-center" style="gap: 8px">
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalForm"><i class="fas fa-plus"></i>
                                            Tambah Konten</button>

                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-responsi table-bordered table-striped data-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" width="30px">No</th>
                                            <th>Jenis Konten</th>
                                            <th>Judul</th>
                                            <th>Gambar</th>
                                            <th>artikel</th>
                                            <th>icon</th>
                                            <th class="text-center" width="100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content workspace-modal">
                <div class="modal-header bg-indigo">
                    <h5 class="modal-title" id="modalFormLabel"></h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formData" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="primary_id" name="primary_id">
                    <div class="modal-body">
                        <div class="form-group row mb-3">
                            <label for="jenis_content" class="col-sm-4 col-form-label">Jenis Konten</label>
                            <div class="col-sm-8">
                                <select id="jenis_content" class="form-control select-jenis" name="jenis_content">
                                    <option value=""></option>
                                    <option value="1">Slider</option>
                                    <option value="2">About Us</option>
                                    <option value="3">Produk</option>
                                    <option value="4">Document</option>
                                    <option value="5">Contact</option>
                                    <option value="6">Fasilitas</option>
                                    <option value="7">Footer</option>
                                    <option value="8">Navbar Item</option>
                                    <option value="9">Navbar Logo</option>
                                    <option value="10">Progres Pembangunan</option>
                                    <option value="11">hero section</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mb-3 form-judul">
                            <label for="judul" class="col-sm-4 col-form-label">Judul</label>
                            <div class="col-sm-8">
                                <input type="text" id="judul" class="form-control" name="judul">
                            </div>
                        </div>

                        <div class="form-group row mb-3 form-url">
                            <label for="url_item" class="col-sm-4 col-form-label">Url</label>
                            <div class="col-sm-8">
                                <input type="text" id="url_item" class="form-control" name="url_item">
                            </div>
                        </div>


                        <div class="form-group row mb-3 form-gambar">
                            <label for="nama_file" class="col-sm-4 col-form-label">Gambar</label>
                            <div class="col-sm-8">
                                <input type="file" id="nama_file" name="nama_file">
                                <img id="preview_gambar" alt="Preview Gambar" class="img-fluid mt-2"
                                    style="object-fit: contain; max-width: 120px; ">
                            </div>
                        </div>
                      
                        <div class="form-group row mb-3 form-file">
                            <label for="nama_file" class="col-sm-4 col-form-label">File</label>
                            <div class="col-sm-8">

                                <div id="file-info" class="mb-2" style="display:none;">
                                    <small class="text-muted">File saat ini:</small><br>
                                    <a href="#" id="file-link" target="_blank" class="fw-bold"></a>
                                </div>

                                <input type="file" id="nama_file" name="nama_file" class="form-control">
                            </div>
                        </div>


                        <div class="form-group row mb-3 form-artikel">
                            <label for="artikel" class="col-sm-4 col-form-label">Artikel</label>
                            <div class="col-sm-8">
                                <textarea id="artikel" class="form-control" name="artikel"></textarea>
                            </div>
                        </div>

                        <div class="form-group row mb-3 form-icon">
                            <label for="icon" class="col-sm-4 col-form-label">Pilih Icon</label>
                            <div class="col-sm-8">
                                <input type="text" id="icon-search" class="form-control mb-2"
                                    placeholder="Cari icon">

                                <div id="icon-picker" class="border p-2"
                                    style="height: 300px; overflow-y: scroll; display: flex; flex-wrap: wrap; gap: 10px;">
                                </div>

                                <input type="hidden" name="icon" id="icon">

                                <div class="mt-4 align-items-center d-flex" style="gap: 10px;">
                                    Icon terpilih: <span id="icon-preview"></span>
                                    <button type="button" id="clear-icon" class="btn btn-xs btn-danger m-2"><i
                                            class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="submitBtn" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm mx-1 d-none" role="status"
                                aria-hidden="true"></span>
                            <span class="button-text">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        let allFontAwesomeIcons = [];

        async function fetchFontAwesomeIcons() {
            const response = await fetch(
                'https://raw.githubusercontent.com/FortAwesome/Font-Awesome/6.x/metadata/icons.json');
            const iconsJson = await response.json();

            allFontAwesomeIcons = Object.keys(iconsJson)
                .filter(name => iconsJson[name].styles.includes('solid'))
                .map(name => `fa-solid fa-${name}`);

            renderIcons(allFontAwesomeIcons);
        }

        function renderIcons(icons) {
            const container = document.getElementById('icon-picker');
            container.innerHTML = '';

            icons.forEach(iconClass => {
                const iconDiv = document.createElement('div');
                iconDiv.classList.add('icon-option');
                iconDiv.setAttribute('data-icon', iconClass);
                iconDiv.style.cssText = `
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid #ccc;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                padding: 0;
                margin: 0;
            `;

                const icon = document.createElement('i');
                icon.className = iconClass;
                iconDiv.appendChild(icon);

                container.appendChild(iconDiv);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const iconInput = document.getElementById('icon');
            const preview = document.getElementById('icon-preview');
            const search = document.getElementById('icon-search');

            fetchFontAwesomeIcons();

            document.getElementById('icon-picker').addEventListener('click', function(e) {
                const target = e.target.closest('.icon-option');
                if (target) {
                    document.querySelectorAll('.icon-option').forEach(i => i.style.backgroundColor = '');
                    target.style.backgroundColor = '#d1e7dd';

                    const selectedIcon = target.getAttribute('data-icon');
                    iconInput.value = selectedIcon;
                    preview.innerHTML = `<i class="${selectedIcon}" style="font-size: 24px;"></i>`;
                }
            });

            search.addEventListener('input', function() {
                const keyword = this.value.toLowerCase();
                const filtered = allFontAwesomeIcons.filter(icon => icon.includes(keyword));
                renderIcons(filtered);
            });
        });

        function updateClearIconVisibility() {
            if ($('#icon').val()) {
                $('#clear-icon').show();
            } else {
                $('#clear-icon').hide();
            }
        }

        $(document).ready(function() {
            updateClearIconVisibility();

            $(document).on('click', '.icon-option', function() {
                const selectedIcon = $(this).data('icon');
                $('#icon').val(selectedIcon);
                $('#icon-preview').html(`<i class="${selectedIcon}" style="font-size: 16px;"></i>`);
                updateClearIconVisibility();
            });

            $('#clear-icon').on('click', function() {
                $('#icon').val('');
                $('#icon-preview').empty();
                updateClearIconVisibility();
            });
        });

        function toggleFieldsBasedOnJenisContent() {
                let jenis = $('#jenis_content').val();
                $('.form-gambar, .form-judul, .form-url, .form-artikel, .form-icon').hide();

                if (['2', '3', '11'].includes(jenis)) {
                    $('.form-gambar, .form-judul, .form-artikel').show();
                } else if (['6'].includes(jenis)) {
                    $('.form-judul, .form-artikel, .form-icon').show();
                } else if (['8'].includes(jenis)) {
                    $('.form-judul').show();
                } else if (['9', '1'].includes(jenis)) {
                    $('.form-gambar').show();
                } else if (['4'].includes(jenis)) {
                    $('.form-judul, .form-file').show();
                } else if (['5'].includes(jenis)) {
                    $('.form-judul, .form-artikel').show();
                } else if (['10'].includes(jenis)) {
                    $('.form-judul, .form-artikel, .form-gambar').show();
            }   else if (['7'].includes(jenis)) {
                    $('.form-judul, .form-artikel').show();
        }
    }

        $(document).ready(function() {
            toggleFieldsBasedOnJenisContent();
        });

        $(document).on('change', '#jenis_content', function() {
            toggleFieldsBasedOnJenisContent();
        });
    </script>


    <script>
        $(document).ready(function() {
            $('.select-jenis').select2({
                theme: "bootstrap4",
                placeholder: "Pilih Jenis Konten",
                minimumResultsForSearch: Infinity,
                    dropdownParent: $('#modalForm') 

            });

              $('#nama_file').on('change', function(e) {
                const file = e.target.files[0];
                
                if(file) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        $('#preview_gambar').attr('src', e.target.result).show();
                    }
                    
                    reader.readAsDataURL(file);
                } else {
                    $('#preview_gambar').attr('src', '').hide();
                }
            });
        });

        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

        $(function() {
            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                ajax: "{{ route('pengaturanLanding.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'jenis_content',
                        name: 'jenis_content',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'judul',
                        name: 'judul',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_file',
                        name: 'nama_file',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'artikel',
                        name: 'artikel',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'icon',
                        name: 'icon',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                    }
                ],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }, ]
            });
        });

         $(document).on('click', '[data-bs-target="#modalForm"]', function() {
            $('#modalFormLabel').text('Tambah Konten');
        });
        

        let originalJenisContent = $('#jenis_content').html();
        const hiddenOnCreate = ['2', '5' ,'7','9','10','11', '8'];

        $(document).on('click', '[data-bs-toggle="modal"][data-bs-target="#modalForm"]', function() {
            if (!$(this).hasClass('edit-button')) {
                $('#modalFormLabel').text('Tambah Konten');
                
                $('#jenis_content').html(originalJenisContent);
                
                hiddenOnCreate.forEach(v => {
                    $('#jenis_content option[value="'+v+'"]').remove();
                });
                
                if ($('#jenis_content').data('select2')) {
                    $('#jenis_content').select2('destroy');
                }
                $('#jenis_content').select2({
                    theme: "bootstrap4",
                    placeholder: "Pilih Jenis Konten",
                    minimumResultsForSearch: Infinity,
                    dropdownParent: $('#modalForm')
                });
                
                $('#jenis_content').val('').trigger('change');
            }
        });

       $(document).on('click', '.edit-button', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            const url = $(this).data('url');

            $.get(url, function(response){
                if(response.status === 'success'){
                    $('#modalFormLabel').text('Edit Konten');
                    
                    $('#jenis_content').html(originalJenisContent);
                    
                    if ($('#jenis_content').data('select2')) {
                        $('#jenis_content').select2('destroy');
                    }
                    $('#jenis_content').select2({
                        theme: "bootstrap4",
                        placeholder: "Pilih Jenis Konten",
                        minimumResultsForSearch: Infinity,
                        dropdownParent: $('#modalForm')
                    });
                    
                    $('#jenis_content').val(response.data.jenis_content).trigger('change');
                    
                    $('#jenis_content').prop('disabled', true);
                    
                    $('#primary_id').val(response.data.id);
                    $('#judul').val(response.data.judul);
                    $('#artikel').val(response.data.artikel);
                    
                    if(response.data.nama_file) {
                        const imagePath = '{{ asset("assets/konten") }}/' + response.data.nama_file;
                        $('#preview_gambar').attr('src', imagePath).show();
                    } else {
                        $('#preview_gambar').attr('src', '').hide();
                    }
                    
                    if(response.data.icon) {
                        $('#icon').val(response.data.icon);
                        $('#icon-preview').html(`<i class="${response.data.icon}" style="font-size: 24px;"></i>`);
                        updateClearIconVisibility();
                    }

                    if (response.data.nama_file) {

                        const fileName = response.data.nama_file;
                        const fileUrl = '{{ asset("assets/konten") }}/' + fileName;

                        $('#file-link')
                            .attr('href', fileUrl)
                            .text(fileName);

                        $('#file-info').show();

                    } else {
                        $('#file-info').hide();
                        $('#file-link').attr('href', '').text('');
                    }

                    
                    toggleFieldsBasedOnJenisContent();
                    
                    $('#modalForm').modal('show');
                }
            }).fail(function() {
                audio.play();
                toastr.error("Gagal mengambil data!", "ERROR", {
                    progressBar: true,
                    timeOut: 3500,
                    positionClass: "toast-bottom-right"
                });
            });
        });

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            $('#primary_id').val('');

            $('#jenis_content option').show();

            $('.select-jenis').val('').trigger('change');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#preview_gambar').hide().attr('src', '');

            $('#jenis_content').prop('disabled', false);

            $('#icon-preview').html('');
            $('#icon').val('');
            $('#icon-search').val('');

            $('#nama_file').val('');
            $('#file-info').hide();
            $('#file-link').attr('href', '').text('');


            let submitBtn = $('#submitBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.addClass('d-none');
            btnText.text('Simpan');
            submitBtn.prop('disabled', false);
        });

        $('#formData').on('submit', function(e) {
            e.preventDefault();

            let id = $('#primary_id').val();
            let url = id ? '{{ route('pengaturanLanding.update', ['pengaturanLanding' => ':id']) }}'.replace(':id', id) :
                '{{ route('pengaturanLanding.store') }}';
            let method = id ? 'PUT' : 'POST';

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let submitBtn = $('#submitBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            let formData = new FormData(this);
            formData.append('_method', method);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function() {
                    $('#modalForm').modal('hide');
                    audio.play();
                    toastr.success("Data telah disimpan!", "BERHASIL", {
                        progressBar: true,
                        timeOut: 3500,
                        positionClass: "toast-bottom-right",
                    });
                    $('.data-table').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        audio.play();
                        toastr.error("Ada inputan yang salah!", "GAGAL!", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });

                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, val) {
                            let input = $('#' + key);
                            input.addClass('is-invalid');
                            input.parent().find('.invalid-feedback').remove();
                            input.parent().append(
                                '<span class="invalid-feedback" role="alert"><strong>' +
                                val[0] + '</strong></span>'
                            );
                        });
                    }

                    spinner.addClass('d-none');
                    btnText.text('Simpan');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        // Hapus data
        $(document).on('click', '.delete-button', function(e) {
            e.preventDefault();

            const form = $(this).closest('form');

            // Cek dulu kalau form-nya valid (punya tombol delete-button)
            if (!form.has('button.delete-button').length) return;

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<span class="swal-btn-text">Ya, Hapus</span>',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: false,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger mx-2',
                    cancelButton: 'btn btn-secondary'
                },
                preConfirm: () => {
                    return new Promise((resolve) => {
                        const confirmBtn = Swal.getConfirmButton();
                        const btnText = confirmBtn.querySelector('.swal-btn-text');

                        btnText.innerHTML = `
                    <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span>
                    Menghapus...`;
                        confirmBtn.disabled = true;

                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: form.serialize(),
                            success: function() {
                                audio.play();
                                toastr.success("Data telah dihapus!", "BERHASIL", {
                                    progressBar: true,
                                    timeOut: 3500,
                                    positionClass: "toast-bottom-right"
                                });

                                $('.data-table').DataTable().ajax.reload(null,
                                    false);
                                Swal.close();
                            },
                            error: function() {
                                audio.play();
                                toastr.error("Gagal menghapus data.", "GAGAL!", {
                                    progressBar: true,
                                    timeOut: 3500,
                                    positionClass: "toast-bottom-right"
                                });

                                btnText.innerHTML = `Ya, Hapus`;
                                confirmBtn.disabled = false;
                            }
                        });
                    });
                }
            });
        });
    </script>
@endpush
