@extends('adminlte::page')

@section('title', 'Edit Jurnal KBM')

@section('content_header')
    <h1>Edit Jurnal KBM</h1>
@stop

@section('content')

<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Form Edit Jurnal</h3>
    </div>

    <form action="{{ route('guru.jurnal.update', $jurnal) }}" method="POST" id="form-jurnal" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="alert alert-info">
                <strong>{{ $jurnal->jadwal->guru->nama }}</strong> —
                {{ $jurnal->jadwal->mapel }} | {{ $jurnal->jadwal->kelas }} |
                {{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d F Y') }}
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @include('guru.jurnal.partials.jp-smart-selection', [
                'jpOptions' => $jpOptions ?? [],
                'selectedJp' => $selectedJp ?? [],
            ])

            @include('guru.jurnal.partials.arabic-language-support')

            <div class="form-group">
                <label>Materi Pembelajaran</label>
                <textarea name="materi" rows="4" class="form-control jurnal-i18n-field jurnal-lang-id" dir="ltr" lang="id" required>{{ old('materi', $jurnal->materi) }}</textarea>
            </div>

            <div class="mb-3">
                <button type="button" class="btn btn-info" id="btn-absensi-siswa">
                    <i class="fas fa-user-check"></i> ABSENSI SISWA
                </button>
            </div>

            @include('guru.jurnal.partials.absensi-form', ['jadwal' => $jurnal->jadwal, 'jurnal' => $jurnal])

            <div class="row">
                @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha'] as $field => $label)
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ $label }}</label>
                            <input type="number" name="{{ $field }}" class="form-control"
                                   value="{{ old($field, $jurnal->$field) }}" min="0" required>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="form-group">
                <label>Status KBM</label>
                <select name="status_kbm" class="form-control">
                    @foreach (['Selesai', 'Belum Selesai', 'Guru Pengganti', 'Libur'] as $status)
                        <option value="{{ $status }}" @selected(old('status_kbm', $jurnal->status_kbm) === $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Catatan Guru</label>
                <textarea name="catatan" rows="3" class="form-control jurnal-i18n-field jurnal-lang-id" dir="ltr" lang="id">{{ old('catatan', $jurnal->catatan) }}</textarea>
            </div>

            @if (! $jurnal->isLocked() && $jurnal->attachments->isNotEmpty())
                <div class="form-group">
                    <label>Caption Lampiran (opsional)</label>
                    @foreach ($jurnal->attachments as $attachment)
                        <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text" title="{{ $attachment->nama_file }}">
                                    <i class="fas fa-image"></i>
                                </span>
                            </div>
                            <input type="text"
                                   name="attachment_captions[{{ $attachment->id }}]"
                                   class="form-control"
                                   maxlength="100"
                                   placeholder="Caption untuk {{ $attachment->nama_file }}"
                                   value="{{ old('attachment_captions.'.$attachment->id, $attachment->caption) }}">
                        </div>
                    @endforeach
                </div>
            @endif

            @include('guru.jurnal.partials.absensi-detail')
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Perbarui Jurnal
            </button>
            <a href="{{ route('guru.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </form>
</div>

@include('guru.jurnal.partials.lampiran-foto-form', ['jurnal' => $jurnal, 'standalone' => true])

@stop
