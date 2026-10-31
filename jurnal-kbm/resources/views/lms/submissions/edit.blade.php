@extends('lms.page')

@section('title', 'Edit Pengumpulan')

@section('content_header')<h1>Edit Pengumpulan</h1>@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card">
        <form method="POST" action="{{ route('lms.submissions.update', $submission) }}">@csrf @method('PUT')
            <div class="card-body">
                <p><strong>{{ e($submission->assignment?->judul) }}</strong> — {{ e($submission->siswa?->nama) }}</p>
                <div class="form-group"><label>URL Drive</label><input type="url" name="drive_url" class="form-control" required value="{{ old('drive_url', $submission->drive_url) }}"></div>
                <div class="form-group"><label>Nilai</label><input type="number" name="score" class="form-control" value="{{ old('score', $submission->score) }}"></div>
                <div class="form-group"><label>Catatan</label><textarea name="catatan" class="form-control">{{ old('catatan', $submission->catatan) }}</textarea></div>
            </div>
            <div class="card-footer"><button class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
@stop
