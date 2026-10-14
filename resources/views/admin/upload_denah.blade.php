@extends('admin.layout')
@section('page_title', 'Upload Denah SQL')
@section('content')
<div class="page-content dashboard-page workspace-page">
    <section class="section">
        <div class="card">
            <div class="card-header workspace-page-header"><div class="workspace-heading"><p class="dashboard-eyebrow">Proyek & kavling</p><h1>Upload Denah SQL</h1><p class="workspace-description">Impor data lokasi, kavling, dan denah proyek.</p></div></div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <p>Upload file .sql berisi lokasi_kavling, kavling_peta, dan/atau master_svg (maksimal 20 MB).</p>
                <form method="POST" action="{{ route('uploaddenah.store') }}" enctype="multipart/form-data" id="upload-denah-form">
                    @csrf
                    <div class="mb-3">
                        <label for="sql_file" class="form-label">File SQL denah</label>
                        <input type="file" id="sql_file" name="sql_file" accept=".sql" class="form-control" required>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" id="reset" name="reset" value="1" class="form-check-input" @checked(old('reset'))>
                        <label for="reset" class="form-check-label">Reset tabel yang terdapat dalam file upload</label>
                    </div>
                    <p class="text-muted">Jika dicentang, seluruh data lama pada tabel tersebut dihapus dan diganti data upload. Jika tidak dicentang, data ditambahkan dengan ID setelah ID terbesar; hubungan lokasi tetap disesuaikan.</p>
                    <button type="submit" class="btn btn-primary" id="upload-button">Upload dan Import</button>
                </form>
            </div>
        </div>
    </section>
</div>
<script>
document.getElementById('upload-denah-form').addEventListener('submit', function(event) {
    if (document.getElementById('reset').checked && !confirm('Seluruh data pada tabel yang terdapat dalam SQL akan dihapus dan diganti. Lanjutkan?')) {
        event.preventDefault();
        return;
    }
    const button = document.getElementById('upload-button');
    button.disabled = true;
    button.textContent = 'Mengimport...';
});
</script>
@endsection
