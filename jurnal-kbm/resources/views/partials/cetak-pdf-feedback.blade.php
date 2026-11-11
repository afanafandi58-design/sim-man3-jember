{{-- Shared loading / validation feedback for Cetak PDF forms --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Gagal mencetak PDF</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div id="cetak-pdf-loading" class="alert alert-info d-none" role="status">
    <i class="fas fa-spinner fa-spin"></i>
    Sedang menyiapkan PDF… Mohon tunggu, jangan tutup halaman ini.
</div>
