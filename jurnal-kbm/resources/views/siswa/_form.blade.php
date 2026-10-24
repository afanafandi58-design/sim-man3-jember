@php
    $isEdit = isset($siswa);
@endphp

<div class="form-group">
    <label>NIS <span class="text-danger">*</span></label>
    <input type="text" name="nis" class="form-control" value="{{ old('nis', $siswa->nis ?? '') }}" required>
</div>

<div class="form-group">
    <label>NISN</label>
    <input type="text" name="nisn" class="form-control" value="{{ old('nisn', $siswa->nisn ?? '') }}">
</div>

<div class="form-group">
    <label>Nama Lengkap <span class="text-danger">*</span></label>
    <input type="text" name="nama" class="form-control" value="{{ old('nama', $siswa->nama ?? '') }}" required>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-control">
                <option value="">-</option>
                <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Tempat Lahir</label>
            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $siswa->tempat_lahir ?? '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', isset($siswa) && $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('Y-m-d') : '') }}">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Kode Kelas <span class="text-danger">*</span></label>
            <select name="kode_kelas" class="form-control" required>
                <option value="">Pilih Kelas</option>
                @foreach ($kelasOptions as $kodeKelas)
                    <option value="{{ $kodeKelas->kode_kelas }}" {{ old('kode_kelas', $siswa->kode_kelas ?? '') === $kodeKelas->kode_kelas ? 'selected' : '' }}>
                        {{ $kodeKelas->kode_kelas }} — {{ $kodeKelas->rombel }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Tahun Pelajaran</label>
            <input type="text" name="tahun_pelajaran" class="form-control" value="{{ old('tahun_pelajaran', $siswa->tahun_pelajaran ?? $defaultTahunPelajaran) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                @foreach ($statusOptions as $status)
                    <option value="{{ $status }}" {{ old('status', $siswa->status ?? \App\Models\Siswa::STATUS_AKTIF) === $status ? 'selected' : '' }}>
                        {{ \App\Models\Siswa::STATUS_LABELS[$status] ?? ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>
