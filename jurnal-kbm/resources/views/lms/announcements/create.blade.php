@extends('adminlte::page')

@section('title', 'Pengumuman Baru')

@section('content_header')<h1>Pengumuman Baru</h1>@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card card-success">
        <form method="POST" action="{{ route('lms.announcements.store') }}">@csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Scope</label>
                    <select name="scope" class="form-control" id="scope">
                        <option value="all">Semua</option>
                        <option value="kelas">Per kelas</option>
                    </select>
                </div>
                <div class="form-group" id="kelas-wrap" style="display:none"><label>Kelas</label><input name="kelas" class="form-control" value="{{ old('kelas') }}"></div>
                <div class="form-group"><label>Judul</label><input name="judul" class="form-control" required value="{{ old('judul') }}"></div>
                <div class="form-group"><label>Isi</label><textarea name="isi" class="form-control" rows="5" required>{{ old('isi') }}</textarea></div>
                <div class="form-group"><label>URL Drive (opsional)</label><input type="url" name="drive_url" class="form-control" value="{{ old('drive_url') }}"></div>
                @if (auth()->user()->role === 'admin')
                    <div class="form-check"><input type="checkbox" name="as_admin" value="1" class="form-check-input" id="adm"><label for="adm">Sebagai admin (tanpa guru_id)</label></div>
                @endif
            </div>
            <div class="card-footer"><button class="btn btn-success">Publikasikan</button></div>
        </form>
    </div>
    <script>
        document.getElementById('scope').addEventListener('change', function () {
            document.getElementById('kelas-wrap').style.display = this.value === 'kelas' ? 'block' : 'none';
        });
    </script>
@stop
