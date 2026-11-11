@php
    $notice = $jurnalRulesNotice ?? null;
@endphp
@if (!empty($notice))
<div class="alert alert-primary border-primary shadow-sm">
    <h5 class="alert-heading mb-2">
        <i class="fas fa-info-circle mr-1"></i>
        {{ $notice['title'] ?? 'Aturan pengisian jurnal' }}
    </h5>
    <ul class="mb-2 pl-3">
        @foreach (($notice['items'] ?? []) as $item)
            <li class="mb-1">{{ $item }}</li>
        @endforeach
    </ul>
    @if (!empty($notice['fillable_range_label']) || !empty($notice['fillable_dates']))
        <p class="mb-0 small">
            <strong>Tanggal jurnal yang bisa diisi:</strong>
            {{ $notice['fillable_range_label'] ?? implode(' s.d. ', $notice['fillable_dates']) }}.
            Tanggal sesudah hari ini tidak bisa diisi.
        </p>
    @endif
</div>
@endif
