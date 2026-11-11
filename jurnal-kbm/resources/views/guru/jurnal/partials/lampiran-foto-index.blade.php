@php
    $firstAttachment = $jurnal->attachments->first();
@endphp

<div class="d-flex align-items-center" style="gap:8px;">
    <span>{{ $jurnal->dokumentasiLabel() }}</span>
    @if ($firstAttachment)
        <img src="{{ $firstAttachment->thumbUrl() }}"
             alt=""
             loading="lazy"
             class="rounded border"
             style="width:36px;height:36px;object-fit:cover;">
    @endif
</div>
