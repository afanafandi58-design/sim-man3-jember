<div class="form-group">
    <label>Jadwal (opsional — isi otomatis kelas & mapel)</label>
    <select name="jadwal_id" class="form-control">
        <option value="">— Manual —</option>
        @foreach ($jadwals as $j)
            <option value="{{ $j->id }}" @selected(old('jadwal_id', optional($model)->jadwal_id) == $j->id)>
                {{ $j->kelas }} · {{ $j->mapel }} · {{ $j->hari }}
            </option>
        @endforeach
    </select>
</div>
<div class="row">
    <div class="col-md-4 form-group">
        <label>Kelas</label>
        <input type="text" name="kelas" class="form-control" required
               value="{{ old('kelas', optional($model)->kelas) }}">
    </div>
    <div class="col-md-4 form-group">
        <label>Mapel</label>
        <input type="text" name="mapel" class="form-control" required
               value="{{ old('mapel', optional($model)->mapel) }}">
    </div>
    <div class="col-md-4 form-group">
        <label>Tahun pelajaran</label>
        <input type="text" name="tahun_pelajaran" class="form-control"
               value="{{ old('tahun_pelajaran', optional($model)->tahun_pelajaran) }}">
    </div>
</div>
