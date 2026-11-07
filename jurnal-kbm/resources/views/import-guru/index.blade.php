@extends('adminlte::page')

@section('title', 'Import Guru')

@section('content_header')
    <h1>Import Data Guru</h1>
@stop

@section('content')

<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('import-data.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Import Data
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Terjadi kesalahan!</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Upload Template Resmi</h3>
    </div>

    <form id="form-import-guru" action="{{ route('import-guru.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card-body">
            @include('partials.import-template-download', [
                'templateUrl' => asset('templates/Template_Import_Guru_SIM_MAN3.xlsx'),
                'downloadName' => 'Template_Import_Guru_SIM_MAN3.xlsx',
            ])

            <div class="alert alert-info">
                <strong>Alur import:</strong>
                <ol class="mb-0 mt-2">
                    <li>Download template resmi (sheet <strong>PETUNJUK</strong> + <strong>DATA</strong>)</li>
                    <li>Isi data mulai baris 3 pada sheet DATA — jangan ubah header</li>
                    <li>Upload file, klik <strong>Preview & Validasi</strong></li>
                    <li>Jika validasi berhasil, tombol <strong>Import Data</strong> akan aktif</li>
                </ol>
            </div>

            <div class="form-group">
                <label for="file">File Excel (.xlsx)</label>
                <div class="custom-file">
                    <input type="file"
                           name="file"
                           id="file"
                           class="custom-file-input @error('file') is-invalid @enderror"
                           accept=".xlsx"
                           required>
                    <label class="custom-file-label" for="file">Pilih file...</label>
                </div>
                @error('file')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>

            @include('partials.import-preview', ['previewId' => 'preview-guru'])

            <div id="import-progress-wrapper" class="d-none mt-3">
                <p class="mb-2"><i class="fas fa-spinner fa-spin"></i> Sedang memproses import...</p>
                <div class="progress">
                    <div id="import-progress-bar"
                         class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                         role="progressbar"
                         style="width: 0%">0%</div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button type="button" id="btn-preview-guru" class="btn btn-info">
                <i class="fas fa-eye"></i> Preview & Validasi
            </button>
            <button type="submit" id="btn-import" class="btn btn-success" disabled>
                <i class="fas fa-file-import"></i> Import Data
            </button>
            <a href="{{ route('import-data.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    document.getElementById('file').addEventListener('change', function () {
        this.nextElementSibling.textContent = this.files.length ? this.files[0].name : 'Pilih file...';
    });

    document.getElementById('form-import-guru').addEventListener('submit', function (e) {
        if (document.getElementById('btn-import').disabled) {
            e.preventDefault();
            alert('Jalankan Preview & Validasi terlebih dahulu.');
            return;
        }

        document.getElementById('import-progress-wrapper').classList.remove('d-none');
        document.getElementById('btn-import').disabled = true;
    });

    setupImportPreview({
        buttonId: 'btn-preview-guru',
        fileId: 'file',
        importButtonId: 'btn-import',
        previewId: 'preview-guru',
        previewUrl: '{{ route('import-guru.preview') }}',
        columns: ['nip', 'nama'],
    });
</script>
@stop
