@extends('adminlte::page')

@section('title', 'Dashboard Monitoring')

@section('content_header')
<h1>Dashboard Monitoring SIM MAN 3 Jember</h1>
@stop

@section('content')

<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('import-data.index') }}" class="btn btn-primary">
            <i class="fas fa-database"></i> Import Data
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $jumlahGuru }}</h3><p>Jumlah Guru</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-teal">
            <div class="inner"><h3>{{ $guruAktif }}</h3><p>Guru Aktif</p></div>
            <div class="icon"><i class="fas fa-user-check"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $guruBelumPunyaAkun }}</h3><p>Belum Punya Akun</p></div>
            <div class="icon"><i class="fas fa-user-times"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ $guruSudahLogin }}</h3><p>Sudah Login</p></div>
            <div class="icon"><i class="fas fa-sign-in-alt"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ $guruBelumLogin }}</h3><p>Belum Login</p></div>
            <div class="icon"><i class="fas fa-user-clock"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $jumlahJadwal }}</h3><p>Total Jadwal</p></div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $jumlahJurnal }}</h3><p>Total Jurnal</p></div>
            <div class="icon"><i class="fas fa-book"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ $jurnalHariIni }}</h3><p>Jurnal Hari Ini</p></div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-teal">
            <div class="inner"><h3>{{ $kbmSelesai }}</h3><p>KBM Selesai</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ $kbmBelum }}</h3><p>KBM Belum</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-md-12">

        <div class="card card-danger">

            <div class="card-header">

                <h3 class="card-title">
                    <i class="fas fa-exclamation-circle"></i>
                    Guru Belum Mengisi Jurnal Hari Ini
                </h3>

            </div>

            <div class="card-body p-0">

                <table class="table table-bordered table-hover">

                    <thead>

                        <tr>
                            <th width="5%">No</th>
                            <th>Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Jam</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                    @forelse($belumIsiJurnal as $item)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $item->guru->nama }}</td>

                            <td>{{ $item->mapel }}</td>

                            <td>{{ $item->kelas }}</td>

                            <td>{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</td>

                            <td>
                                <span class="badge badge-danger">
                                    Belum Mengisi
                                </span>
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center text-success">

                                <strong>
                                    🎉 Semua guru sudah mengisi jurnal hari ini.
                                </strong>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@stop