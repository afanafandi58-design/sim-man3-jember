@extends('adminlte::page')

@section('title', 'Rekap Beban Mengajar')

@section('content_header')
    <h1>Rekap Beban Mengajar</h1>
    <small class="text-muted">Tahun Pelajaran {{ $tahunPelajaran }}</small>
@stop

@section('content')

<div class="card">
    <div class="card-header"><h3 class="card-title">Filter</h3></div>
    <div class="card-body">
        <form action="{{ route('guru-beban-mengajar.index') }}" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="nama" class="form-control" placeholder="Cari Nama" value="{{ request('nama') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="nip" class="form-control" placeholder="Cari NIP" value="{{ request('nip') }}">
                </div>
                <div class="col-md-2">
                    <select name="mapel" class="form-control">
                        <option value="">Semua Mapel</option>
                        @foreach ($mapels as $mapel)
                            <option value="{{ $mapel }}" {{ request('mapel') === $mapel ? 'selected' : '' }}>{{ $mapel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="belum" {{ request('status') === 'belum' ? 'selected' : '' }}>Belum Punya Akun</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success btn-block"><i class="fas fa-search"></i> Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>No</th>
                    <th>Kode Guru</th>
                    <th>Nama Guru</th>
                    <th>NIP</th>
                    <th>Mapel</th>
                    <th>Jam Mengajar</th>
                    <th>Jumlah Kelas</th>
                    <th>Status</th>
                    <th>Jurnal Dibuat</th>
                    <th>Progress KBM</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $items->firstItem() + $loop->index }}</td>
                        <td>{{ $item['kode_guru'] }}</td>
                        <td>{{ $item['nama'] }}</td>
                        <td>{{ $item['nip'] }}</td>
                        <td>{{ $item['mapel'] }}</td>
                        <td>{{ $item['jumlah_jam_mengajar'] }} JP</td>
                        <td>{{ $item['jumlah_kelas'] }}</td>
                        <td>{{ $item['status'] }}</td>
                        <td>{{ $item['jumlah_jurnal'] }}</td>
                        <td>
                            <div class="progress" style="min-width:120px;height:18px;">
                                <div class="progress-bar bg-success" style="width: {{ $item['progress_kbm'] }}%">
                                    {{ $item['progress_kbm'] }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-center">
        {{ $items->appends(request()->query())->links() }}
    </div>
</div>

@stop
