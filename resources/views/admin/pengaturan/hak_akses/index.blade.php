@extends('admin.layout')
@section('page_title', 'Hak Akses')
@section('content')
    <div class="page-content dashboard-page workspace-page">
        <section class="section">
            <div class="card">
                <div class="card-header workspace-page-header">
                    <div class="d-flex align-content-center justify-content-between">
                        <div class="workspace-heading"><p class="dashboard-eyebrow">Pengaturan</p><h1>Hak Akses</h1><p class="workspace-description">Atur akses menu dan tindakan untuk setiap pengguna.</p></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3 align-items-center">
                        <label for="" class="col-sm-2 col-form-label">Pilih Pengguna</label>
                        <div class="col-sm-3">
                            <select class="form-select select-pengguna" id="pilih-pengguna">
                                <option value="" selected disabled>Pilih</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->username }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table data-table">
                            <thead>
                                <tr>
                                    <th width="50px">No</th>
                                    <th>ID</th>
                                    <th width="200px">Induk Menu</th>
                                    <th>Judul Menu</th>
                                    <th>Route</th>
                                    <th width="70px">Lihat</th>
                                    <th width="70px">Beranda</th>
                                    <th width="70px">Tambah</th>
                                    <th width="70px">Edit</th>
                                    <th width="70px">Hapus</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        @if ($permissions['edit'] == 1)
                            <div class="text-left mt-4 mb-4">
                                <button type="submit" class="btn btn-primary ms-1" id="btn-simpan" disabled>
                                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                                        aria-hidden="true"></span>
                                    <span class="button-text">Simpan Hak Akses</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select-pengguna').select2({
                width: '100%',
                placeholder: 'Pilih Pengguna',
                allowClear: true,
            });

            if ($('body').hasClass('dark')) {
                $('.select2-container').addClass('select2-dark');
            }
        });

        $(document).ready(function() {
            const selectedUserId = localStorage.getItem('selectedUserId');
            if (selectedUserId) {
                $('#pilih-pengguna').val(selectedUserId).trigger('change');
                localStorage.removeItem('selectedUserId');
            }

            const successMsg = localStorage.getItem('hakAksesSuccess');
            if (successMsg) {
                audio.play();
                toastr.success(successMsg, "BERHASIL", {
                    progressBar: true,
                    timeOut: 3500,
                    positionClass: "toast-bottom-right",
                });
                localStorage.removeItem('hakAksesSuccess');
            }

        });

        var btnSimpan = $('#btn-simpan');

        btnSimpan.prop('disabled', true);

        $('#pilih-pengguna').change(function() {
            if ($(this).val()) {
                btnSimpan.prop('disabled', false);
            } else {
                btnSimpan.prop('disabled', true);
            }
        });

        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

        var permissions = @json($permissions);

        var hakAksesState = {
            lihat: {},
            beranda: {},
            tambah: {},
            edit: {},
            hapus: {}
        };

        var table = $('.data-table').DataTable({
            processing: false,
            serverSide: true,
            ordering: false,
            responsive: true,
            ajax: {
                url: "{{ route('admin.getHakAkses') }}",
                data: function(d) {
                    d.id_user = $('#pilih-pengguna').val();
                    d.permissions = permissions;
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'id',
                    name: 'id',
                    orderable: false,
                    searchable: false,
                    visible: false
                },
                {
                    data: 'induk_menu',
                    name: 'induk_menu',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'title',
                    name: 'title',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'route_name',
                    name: 'route_name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'lihat',
                    name: 'lihat',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'beranda',
                    name: 'beranda',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'tambah',
                    name: 'tambah',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'edit',
                    name: 'edit',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'hapus',
                    name: 'hapus',
                    orderable: false,
                    searchable: false
                }
            ],
            columnDefs: [{
                targets: 0,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            }]
        });


        $('#pilih-pengguna').change(function() {
            Object.keys(hakAksesState).forEach(function(key) { hakAksesState[key] = {}; });
            table.draw();
        });


        $('.data-table').on('draw.dt', function() {
            $('.data-table tbody input[type="checkbox"]').each(function() {
                const match = this.name.match(/(\w+)\[(\d+)\]/);
                if (match && Object.prototype.hasOwnProperty.call(hakAksesState[match[1]] || {}, match[2])) {
                    this.checked = hakAksesState[match[1]][match[2]] === 1;
                }
            });
            $(document).off('change', 'input[name^="lihat"]').on('change', 'input[name^="lihat"]', function() {
                let rowId = $(this).attr('name').match(/\[(.*?)\]/)[1];
                let checked = $(this).is(':checked');

                $(`input[name="tambah[${rowId}]"], input[name="edit[${rowId}]"], input[name="hapus[${rowId}]"]`)
                    .prop('checked', checked);
                ['tambah', 'edit', 'hapus'].forEach(function(type) {
                    if ($(`input[name="${type}[${rowId}]"]`).length) hakAksesState[type][rowId] = checked ? 1 : 0;
                });
            });

            $(document).off('change', 'input[name^="tambah"], input[name^="edit"], input[name^="hapus"]').on(
                'change', 'input[name^="tambah"], input[name^="edit"], input[name^="hapus"]',
                function() {
                    let rowId = $(this).attr('name').match(/\[(.*?)\]/)[1];
                    let lihatCheckbox = $(`input[name="lihat[${rowId}]"]`);

                    if ($(this).is(':checked') && !lihatCheckbox.is(':checked')) {
                        lihatCheckbox.prop('checked', true);
                        hakAksesState.lihat[rowId] = 1;
                    }
                });
        });

        $(document).on('change', '.data-table tbody input[type="checkbox"]', function() {
            let name = $(this).attr('name');
            let match = name.match(/(\w+)\[(\d+)\]/);
            if (match) {
                let type = match[1];
                let id = match[2];
                hakAksesState[type][id] = $(this).is(':checked') ? 1 : 0;
            }
        });

        btnSimpan.click(function() {
            let submitBtn = $('#btn-simpan');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            var id_user = $('#pilih-pengguna').val();

            hakAksesState = {
                lihat: {},
                beranda: {},
                tambah: {},
                edit: {},
                hapus: {}
            };

            $('.data-table tbody input[type="checkbox"]').each(function() {
                let name = $(this).attr('name');
                let match = name.match(/(\w+)\[(\d+)\]/);
                if (match) {
                    let type = match[1];
                    let id = match[2];
                    hakAksesState[type][id] = $(this).is(':checked') ? 1 : 0;
                }
            });

            $.ajax({
                url: '{{ route('admin.updateHakAkses') }}',
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_user: id_user,
                    hak_akses_data: hakAksesState
                },
                success: function(response) {
                    if (response.success) {
                        localStorage.setItem('hakAksesSuccess', response.message);
                        localStorage.setItem('selectedUserId', id_user);
                        location.reload();
                    } else {
                        audio.play();
                        toastr.error("Terjadi kesalahan saat memperbarui Hak Akses.", "GAGAL!", {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: "toast-bottom-right",
                        });
                    }
                },
                error: function() {
                    toastr.error("Terjadi kesalahan saat menghubungi server.", "GAGAL!", {
                        progressBar: true,
                        timeOut: 3500,
                        positionClass: "toast-bottom-right",
                    });
                    spinner.addClass('d-none');
                    btnText.text('Simpan');
                    submitBtn.prop('disabled', false);
                }
            });
        });
    </script>
@endpush
