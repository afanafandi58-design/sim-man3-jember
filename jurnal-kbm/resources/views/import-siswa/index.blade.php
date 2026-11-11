@extends('adminlte::page')

@section('title', 'Import Data Siswa')

@section('content_header')
    <h1>Import Data Siswa</h1>
@stop

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('import-data.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Import Data
        </a>
    </div>
</div>

@if(session('import_kode_kelas_result'))
    @php $result = session('import_kode_kelas_result'); @endphp
    <div class="alert alert-info">
        Import Kode Kelas: {{ $result['success'] }}/{{ $result['total'] }} berhasil.
        @if($result['failed'] > 0)
            <br>Gagal: {{ $result['failed'] }}
        @endif
    </div>
@endif

@if(session('import_siswa_result'))
    @php $result = session('import_siswa_result'); @endphp
    <div class="alert alert-success">
        Import Siswa: {{ $result['success'] }}/{{ $result['total'] }} berhasil.
        @if($result['failed'] > 0)
            <br>Gagal: {{ $result['failed'] }}
        @endif
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">3. Import Kode Kelas</h3>
            </div>
            <form id="form-import-kelas" action="{{ route('import-siswa.kode-kelas') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    @include('partials.import-template-download', [
                        'templateUrl' => asset('templates/Template_Import_Kelas_SIM_MAN3.xlsx'),
                        'downloadName' => 'Template_Import_Kelas_SIM_MAN3.xlsx',
                    ])

                    <p class="text-muted">Master: kode_kelas, kelas, rombel — sheet PETUNJUK + DATA</p>
                    <input type="file" name="file" id="file-kode-kelas" class="form-control" accept=".xlsx" required>
                    @error('file') <small class="text-danger">{{ $message }}</small> @enderror

                    @include('partials.import-preview', ['previewId' => 'preview-kode-kelas'])
                </div>
                <div class="card-footer">
                    <button type="button" id="btn-preview-kode-kelas" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i> Preview & Validasi
                    </button>
                    <button type="submit" id="btn-import-kelas" class="btn btn-primary btn-sm" disabled>
                        Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">4. Import Siswa</h3>
            </div>
            <form id="form-import-siswa" action="{{ route('import-siswa.siswa') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    @include('partials.import-template-download', [
                        'templateUrl' => asset('templates/Template_Import_Siswa_SIM_MAN3.xlsx'),
                        'downloadName' => 'Template_Import_Siswa_SIM_MAN3.xlsx',
                    ])

                    <p class="text-muted">Dropdown: jenis_kelamin, status, kode_kelas dari master</p>
                    <input type="file" name="file" id="file-siswa" class="form-control" accept=".xlsx" required>
                    @error('file_siswa') <small class="text-danger">{{ $message }}</small> @enderror

                    @include('partials.import-preview', ['previewId' => 'preview-siswa'])
                </div>
                <div class="card-footer">
                    <button type="button" id="btn-preview-siswa" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i> Preview & Validasi
                    </button>
                    <button type="submit" id="btn-import-siswa" class="btn btn-success btn-sm" disabled>
                        Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    ['form-import-kelas', 'form-import-siswa'].forEach(function (formId) {
        const form = document.getElementById(formId);
        const importBtn = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function (e) {
            if (importBtn.disabled) {
                e.preventDefault();
                alert('Jalankan Preview & Validasi terlebih dahulu.');
            }
        });
    });

    setupImportPreview({
        buttonId: 'btn-preview-kode-kelas',
        fileId: 'file-kode-kelas',
        importButtonId: 'btn-import-kelas',
        previewId: 'preview-kode-kelas',
        previewUrl: '{{ route('import-siswa.kode-kelas.preview') }}',
        columns: ['kode_kelas', 'kelas', 'rombel'],
    });

    setupImportPreview({
        buttonId: 'btn-preview-siswa',
        fileId: 'file-siswa',
        importButtonId: 'btn-import-siswa',
        previewId: 'preview-siswa',
        previewUrl: '{{ route('import-siswa.siswa.preview') }}',
        columns: ['nis', 'nama', 'jenis_kelamin', 'kode_kelas', 'tanggal_lahir', 'status'],
    });
</script>
@stop
