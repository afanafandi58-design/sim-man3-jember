@extends('lms.page')

@section('title', 'Catat Pengumpulan')

@section('content_header')<h1>Catat Pengumpulan</h1>@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card card-success">
        <form method="POST" action="{{ route('lms.submissions.store') }}">@csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Tugas</label>
                    <select name="lms_assignment_id" class="form-control" required>
                        @foreach ($assignments as $a)
                            <option value="{{ $a->id }}" @selected(old('lms_assignment_id') == $a->id)>{{ $a->judul }} ({{ $a->kelas }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Siswa</label>
                    <select name="siswa_id" class="form-control" required>
                        @foreach ($siswas as $siswa)
                            <option value="{{ $siswa->id }}" @selected(old('siswa_id') == $siswa->id)>{{ $siswa->nis }} — {{ $siswa->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>URL Drive jawaban</label><input type="url" name="drive_url" class="form-control" required value="{{ old('drive_url') }}"></div>
                <div class="form-group"><label>Nilai</label><input type="number" name="score" class="form-control" value="{{ old('score') }}"></div>
                <div class="form-group"><label>Catatan</label><textarea name="catatan" class="form-control">{{ old('catatan') }}</textarea></div>
            </div>
            <div class="card-footer"><button class="btn btn-success">Simpan</button></div>
        </form>
    </div>
@stop
