@if(($absensiDetail['total'] ?? 0) > 0)
<div class="card card-outline card-secondary mt-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-user-times"></i> Detail Absensi Siswa
        </h3>
    </div>
    <div class="card-body">
        @foreach (['Sakit', 'Izin', 'Alpha'] as $status)
            @if(!empty($absensiDetail['grouped'][$status]))
                <h6 class="text-uppercase mt-2">{{ $status }}</h6>
                <ul class="mb-2">
                    @foreach($absensiDetail['grouped'][$status] as $nama)
                        <li>{{ $nama }}</li>
                    @endforeach
                </ul>
            @endif
        @endforeach

        <hr>
        <div class="row">
            <div class="col-md-3"><strong>Sakit:</strong> {{ $absensiDetail['counts']['Sakit'] ?? 0 }}</div>
            <div class="col-md-3"><strong>Izin:</strong> {{ $absensiDetail['counts']['Izin'] ?? 0 }}</div>
            <div class="col-md-3"><strong>Alpha:</strong> {{ $absensiDetail['counts']['Alpha'] ?? 0 }}</div>
            <div class="col-md-3"><strong>Hadir:</strong> {{ $absensiDetail['counts']['Hadir'] ?? 0 }}</div>
        </div>
    </div>
</div>
@endif
