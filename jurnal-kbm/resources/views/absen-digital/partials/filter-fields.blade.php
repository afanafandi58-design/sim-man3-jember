<div class="form-row">
    <div class="form-group col-md-3">
        <label for="tanggal_dari">Tanggal dari</label>
        <input id="tanggal_dari" type="date" name="tanggal_dari" class="form-control" value="{{ $filters['tanggal_dari'] }}">
    </div>
    <div class="form-group col-md-3">
        <label for="tanggal_sampai">Tanggal sampai</label>
        <input id="tanggal_sampai" type="date" name="tanggal_sampai" class="form-control" value="{{ $filters['tanggal_sampai'] }}">
    </div>
    <div class="form-group col-md-3">
        <label for="jenis">Jenis</label>
        <select id="jenis" name="jenis" class="form-control">
            <option value="">Semua</option>
            @foreach (\App\Models\AbsenKegiatan::JENIS as $key => $label)
                <option value="{{ $key }}" @selected($filters['jenis'] === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-3">
        <label for="kegiatan_id">Kegiatan</label>
        <select id="kegiatan_id" name="kegiatan_id" class="form-control">
            <option value="">Semua</option>
            @foreach ($semuaKegiatan as $item)
                <option value="{{ $item->id }}" @selected((string) $filters['kegiatan_id'] === (string) $item->id)>
                    {{ $item->tanggal?->format('d/m/Y') }} — {{ $item->nama }}
                </option>
            @endforeach
        </select>
    </div>
</div>
