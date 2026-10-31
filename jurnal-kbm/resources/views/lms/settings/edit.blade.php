@extends('lms.page')

@section('title', 'Pengaturan LMS')

@section('content_header')<h1>Pengaturan LMS & Google Drive</h1>@stop

@section('content')
    @include('lms.partials.flash')
    <div class="alert alert-info">
        Mode default: <strong>link manual</strong> ke Google Drive. OAuth API aktif hanya jika env <code>GOOGLE_CLIENT_ID</code> dan <code>GOOGLE_CLIENT_SECRET</code> diisi di server.
        Status OAuth env: {{ $oauthConfigured ? 'terdeteksi' : 'belum dikonfigurasi' }}.
    </div>
    <div class="card card-success">
        <form method="POST" action="{{ route('lms.settings.update') }}">@csrf @method('PUT')
            <div class="card-body">
                <div class="form-group"><label>URL folder Drive induk (opsional)</label><input type="url" name="drive_folder_url" class="form-control" value="{{ old('drive_folder_url', $settings['drive_folder_url']) }}"></div>
                <div class="form-group"><label>Instruksi untuk guru</label><textarea name="share_instructions" class="form-control" rows="4">{{ old('share_instructions', $settings['share_instructions']) }}</textarea></div>
                <div class="form-check"><input type="checkbox" name="manual_links_only" value="1" class="form-check-input" id="man" @checked($settings['manual_links_only'] === '1')><label for="man">Wajib link manual (non-OAuth)</label></div>
            </div>
            <div class="card-footer"><button class="btn btn-success">Simpan</button></div>
        </form>
    </div>
@stop
