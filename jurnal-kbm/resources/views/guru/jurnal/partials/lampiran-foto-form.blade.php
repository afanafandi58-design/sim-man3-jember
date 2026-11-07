@php
    use App\Models\JurnalAttachment;

    $jurnalModel = $jurnal ?? null;
    $standalone = $standalone ?? false;
    $canModify = $jurnalModel ? ! $jurnalModel->isLocked() : true;
    $attachments = $jurnalModel?->attachments ?? collect();
    $remaining = JurnalAttachment::MAX_PER_JURNAL - $attachments->count();
@endphp

<div class="card card-outline card-info mt-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-camera"></i> Lampiran Foto Kegiatan
        </h3>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Format: JPG, PNG, WebP. Maksimal 2 MB per file. Maksimal {{ JurnalAttachment::MAX_PER_JURNAL }} foto per jurnal.
            Foto dioptimalkan otomatis (resize 1280px, kualitas ~80%).
            @if ($jurnalModel && $jurnalModel->isLocked())
                <br><span class="text-warning"><i class="fas fa-lock"></i> Jurnal terkunci — lampiran hanya dapat dilihat.</span>
            @endif
        </p>

        @if ($attachments->isNotEmpty())
            <div class="row mb-3" id="lampiran-preview-grid">
                @foreach ($attachments as $attachment)
                    <div class="col-6 col-md-4 mb-3">
                        <div class="border rounded p-2 h-100 bg-light">
                            <a href="{{ $attachment->url() }}" target="_blank" rel="noopener">
                                <img src="{{ $attachment->thumbUrl() }}"
                                     alt="{{ $attachment->nama_file }}"
                                     loading="lazy"
                                     class="img-fluid rounded mb-2"
                                     style="max-height:100px;object-fit:cover;width:100%;">
                            </a>
                            @if (! $standalone && $canModify)
                                <input type="text"
                                       name="attachment_captions[{{ $attachment->id }}]"
                                       class="form-control form-control-sm mb-2"
                                       maxlength="100"
                                       placeholder="Caption (opsional)"
                                       value="{{ old('attachment_captions.'.$attachment->id, $attachment->caption) }}">
                            @elseif ($attachment->caption)
                                <div class="small font-italic mb-2">{{ $attachment->caption }}</div>
                            @endif
                            @if ($standalone && $canModify)
                                <form action="{{ route('guru.jurnal.lampiran.destroy', [$jurnalModel, $attachment]) }}"
                                      method="POST"
                                      class="mt-1"
                                      onsubmit="return confirm('Hapus lampiran foto ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger btn-block">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($canModify && $remaining > 0)
            @if ($standalone && $jurnalModel)
                <form action="{{ route('guru.jurnal.lampiran.store', $jurnalModel) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      id="form-lampiran-upload">
                    @csrf
                    <div class="form-group mb-0">
                        <label for="lampiran-input">Unggah Foto (sisa {{ $remaining }} slot)</label>
                        <input type="file"
                               name="lampiran[]"
                               id="lampiran-input"
                               class="form-control-file"
                               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                               multiple
                               data-max-files="{{ $remaining }}">
                        <small class="form-text text-muted">Caption opsional per foto (maks. 100 karakter).</small>
                    </div>
                    <div class="row mt-3" id="lampiran-new-preview"></div>
                    <button type="submit" class="btn btn-info btn-sm mt-3">
                        <i class="fas fa-upload"></i> Unggah Foto
                    </button>
                </form>
            @else
                <div class="form-group mb-0">
                    <label for="lampiran-input">Unggah Foto (sisa {{ $remaining }} slot)</label>
                    <input type="file"
                           name="lampiran[]"
                           id="lampiran-input"
                           class="form-control-file"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                           multiple
                           data-max-files="{{ $remaining }}">
                    <small class="form-text text-muted">Caption opsional per foto (maks. 100 karakter).</small>
                </div>
                <div class="row mt-3" id="lampiran-new-preview"></div>
            @endif
        @elseif ($canModify)
            <p class="text-muted mb-0"><i class="fas fa-info-circle"></i> Kuota foto sudah penuh ({{ JurnalAttachment::MAX_PER_JURNAL }}/{{ JurnalAttachment::MAX_PER_JURNAL }}).</p>
        @elseif ($attachments->isEmpty())
            <p class="text-muted mb-0">Tidak ada dokumentasi pembelajaran.</p>
        @endif
    </div>
</div>

@if ($canModify && $remaining > 0)
    @push('js')
    <script>
        document.getElementById('lampiran-input')?.addEventListener('change', function () {
            const preview = document.getElementById('lampiran-new-preview');
            const maxFiles = parseInt(this.dataset.maxFiles || '3', 10);
            if (!preview) return;
            preview.innerHTML = '';

            const files = Array.from(this.files).slice(0, maxFiles);
            if (this.files.length > maxFiles) {
                alert('Maksimal ' + maxFiles + ' foto dapat diunggah.');
                this.value = '';
                return;
            }

            files.forEach(function (file, index) {
                if (!file.type.startsWith('image/')) return;

                const col = document.createElement('div');
                col.className = 'col-6 col-md-4 mb-3';

                const wrap = document.createElement('div');
                wrap.className = 'border rounded p-2 bg-white';

                const img = document.createElement('img');
                img.className = 'img-fluid rounded border mb-2';
                img.style.maxHeight = '100px';
                img.style.objectFit = 'cover';
                img.style.width = '100%';
                img.loading = 'lazy';
                img.src = URL.createObjectURL(file);

                const caption = document.createElement('input');
                caption.type = 'text';
                caption.name = 'lampiran_caption[]';
                caption.className = 'form-control form-control-sm';
                caption.maxLength = 100;
                caption.placeholder = 'Caption foto ' + (index + 1) + ' (opsional)';

                wrap.appendChild(img);
                wrap.appendChild(caption);
                col.appendChild(wrap);
                preview.appendChild(col);
            });
        });
    </script>
    @endpush
@endif
