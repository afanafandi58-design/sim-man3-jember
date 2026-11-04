<form method="GET" action="{{ $action }}" class="card">
    <div class="card-body">
        @include('absen-digital.partials.filter-fields', ['filters' => $filters, 'semuaKegiatan' => $semuaKegiatan])
    </div>
    <div class="card-footer">
        <button class="btn btn-primary" type="submit">Terapkan filter</button>
        <a class="btn btn-default" href="{{ $action }}">Bulan ini</a>
    </div>
</form>
