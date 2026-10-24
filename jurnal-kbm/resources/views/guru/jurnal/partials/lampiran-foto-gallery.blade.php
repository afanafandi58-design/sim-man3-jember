@php
    $attachments = ($jurnal->attachments ?? collect())->take(\App\Models\JurnalAttachment::MAX_PER_JURNAL);
    $modalId = 'lampiranModal'.($jurnal->id ?? 'x');
@endphp

<div class="card card-outline card-info mt-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-camera"></i> Dokumentasi Pembelajaran
        </h3>
    </div>
    <div class="card-body">
        @if ($attachments->isNotEmpty())
            <div class="row">
                @foreach ($attachments as $attachment)
                    <div class="col-6 col-md-4 mb-3">
                        <a href="#"
                           class="d-block lampiran-thumb"
                           data-toggle="modal"
                           data-target="#{{ $modalId }}"
                           data-full="{{ $attachment->url() }}"
                           data-name="{{ $attachment->caption ?: 'Foto Kegiatan' }}">
                            <img src="{{ $attachment->thumbUrl() }}"
                                 alt="{{ $attachment->caption ?: 'Foto kegiatan' }}"
                                 loading="lazy"
                                 class="img-fluid rounded border"
                                 style="height:90px;width:100%;object-fit:cover;cursor:pointer;">
                        </a>
                        @if ($attachment->caption)
                            <div class="small mt-1">{{ $attachment->caption }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="{{ $modalId }}-title">Foto Kegiatan</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="" alt="" loading="lazy" class="img-fluid rounded" id="{{ $modalId }}-img">
                        </div>
                    </div>
                </div>
            </div>

            @push('js')
            <script>
                document.querySelectorAll('.lampiran-thumb[data-target="#{{ $modalId }}"]').forEach(function (el) {
                    el.addEventListener('click', function (e) {
                        e.preventDefault();
                        const img = document.getElementById('{{ $modalId }}-img');
                        img.src = this.dataset.full;
                        document.getElementById('{{ $modalId }}-title').textContent = this.dataset.name || 'Foto Kegiatan';
                    });
                });
            </script>
            @endpush
        @else
            <p class="text-muted mb-0">Tidak ada dokumentasi pembelajaran.</p>
        @endif
    </div>
</div>
