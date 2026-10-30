@extends('lms.public.layout')

@section('title', 'Kirim Tugas')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h5>Kirim: {{ e($assignment->judul) }}</h5>
            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('lms.public.submit.store', $assignment->share_token) }}">
                @csrf
                <div class="form-group"><label>NIS</label><input name="nis" class="form-control" required value="{{ old('nis') }}"></div>
                <div class="form-group"><label>URL Google Drive jawaban (HTTPS)</label><input type="url" name="drive_url" class="form-control" required value="{{ old('drive_url') }}"></div>
                <div class="form-group"><label>Catatan (opsional)</label><textarea name="catatan" class="form-control" rows="2">{{ old('catatan') }}</textarea></div>
                <button class="btn btn-success">Kirim</button>
            </form>
        </div>
    </div>
@endsection
