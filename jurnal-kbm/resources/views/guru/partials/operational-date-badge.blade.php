@if ($operationalDate['showBadge'] ?? false)
<div class="alert alert-warning py-2 px-3 mb-3 border border-warning">
    <div class="font-weight-bold mb-1">🟡 MODE UJI COBA (UAT)</div>
    <div class="small mb-0">
        <div>Hari Operasional : <strong>{{ $operationalDate['hariOperasional'] ?? '-' }}</strong></div>
        <div>Tanggal Sistem : <strong>{{ $operationalDate['tanggalSistem'] ?? '-' }}</strong></div>
        <div>Database : <strong>PRODUKSI</strong></div>
    </div>
</div>
@endif
