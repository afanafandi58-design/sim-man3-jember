@php
    $selectedJp = collect($selectedJp ?? old('jp_selected', []))
        ->map(fn ($value) => (int) $value)
        ->values()
        ->all();
    $jpOptions = $jpOptions ?? [];
@endphp

<div class="form-group" id="jp-smart-selection">
    <label class="d-block">Jam Pelajaran <span class="text-danger">*</span></label>
    <small class="text-muted d-block mb-2">Pilih satu atau lebih JP berurutan. Klik JP awal lalu JP akhir untuk rentang otomatis.</small>

    @error('jp_selected')
        <div class="alert alert-danger py-2">{{ $message }}</div>
    @enderror

    <div class="jp-toggle-grid mb-3">
        @forelse ($jpOptions as $jp)
            @php $isSelected = in_array((int) $jp['jam_ke'], $selectedJp, true); @endphp
            <button type="button"
                    class="btn jp-toggle-btn {{ $isSelected ? 'btn-success active' : 'btn-outline-secondary' }}"
                    data-jp="{{ $jp['jam_ke'] }}"
                    aria-pressed="{{ $isSelected ? 'true' : 'false' }}">
                JP {{ str_pad((string) $jp['jam_ke'], 2, '0', STR_PAD_LEFT) }}
            </button>
        @empty
            <div class="alert alert-warning mb-0">Master Jam Pelajaran belum tersedia untuk pola jadwal ini.</div>
        @endforelse
    </div>

    <div id="jp-selected-inputs">
        @foreach ($selectedJp as $jp)
            <input type="hidden" name="jp_selected[]" value="{{ $jp }}">
        @endforeach
    </div>

    <div class="card card-outline card-success">
        <div class="card-body py-2">
            <div class="row">
                <div class="col-md-4">
                    <strong>Jam Dipilih</strong>
                    <div id="jp-summary-label" class="text-success">-</div>
                </div>
                <div class="col-md-4">
                    <strong>Total</strong>
                    <div id="jp-summary-count">0 JP</div>
                </div>
                <div class="col-md-4">
                    <strong>Waktu</strong>
                    <div id="jp-summary-time">-</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="jpRangeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rentang Jam Pelajaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="jp-range-question" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="jp-range-no">Tidak</button>
                <button type="button" class="btn btn-success" id="jp-range-yes">Ya</button>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
    .jp-toggle-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(72px, 1fr));
        gap: 8px;
    }
    .jp-toggle-btn {
        min-height: 42px;
        font-weight: 600;
    }
    .jp-toggle-btn.active {
        box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.35);
    }
    @media (max-width: 576px) {
        .jp-toggle-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
</style>
@endpush

@push('js')
<script>
(function () {
    const jpOptions = @json(array_values($jpOptions));
    const jpMap = {};
    jpOptions.forEach(function (item) {
        jpMap[String(item.jam_ke)] = item;
    });

    const container = document.getElementById('jp-smart-selection');
    if (!container || jpOptions.length === 0) {
        return;
    }

    const inputsHost = document.getElementById('jp-selected-inputs');
    const summaryLabel = document.getElementById('jp-summary-label');
    const summaryCount = document.getElementById('jp-summary-count');
    const summaryTime = document.getElementById('jp-summary-time');
    const buttons = Array.from(container.querySelectorAll('.jp-toggle-btn'));
    const form = container.closest('form');

    let selected = Array.from(inputsHost.querySelectorAll('input[name="jp_selected[]"]'))
        .map(function (input) { return parseInt(input.value, 10); })
        .filter(function (value) { return !Number.isNaN(value); })
        .sort(function (a, b) { return a - b; });

    let anchorJp = null;
    let pendingRange = null;

    function isConsecutive(values) {
        if (values.length <= 1) {
            return true;
        }
        for (let i = 1; i < values.length; i++) {
            if (values[i] !== values[i - 1] + 1) {
                return false;
            }
        }
        return true;
    }

    function rangeValues(from, to) {
        const start = Math.min(from, to);
        const end = Math.max(from, to);
        const values = [];
        for (let i = start; i <= end; i++) {
            if (jpMap[String(i)]) {
                values.push(i);
            }
        }
        return values;
    }

    function syncInputs() {
        inputsHost.innerHTML = '';
        selected.forEach(function (jp) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'jp_selected[]';
            input.value = jp;
            inputsHost.appendChild(input);
        });

        buttons.forEach(function (button) {
            const jp = parseInt(button.dataset.jp, 10);
            const active = selected.includes(jp);
            button.classList.toggle('btn-success', active);
            button.classList.toggle('active', active);
            button.classList.toggle('btn-outline-secondary', !active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (selected.length === 0) {
            summaryLabel.textContent = '-';
            summaryCount.textContent = '0 JP';
            summaryTime.textContent = '-';
            return;
        }

        const first = selected[0];
        const last = selected[selected.length - 1];
        summaryLabel.textContent = first === last ? ('JP ' + first) : ('JP ' + first + ' - JP ' + last);
        summaryCount.textContent = selected.length + ' JP';

        const firstData = jpMap[String(first)];
        const lastData = jpMap[String(last)];
        summaryTime.textContent = firstData && lastData
            ? (firstData.jam_mulai + ' - ' + lastData.jam_selesai)
            : '-';
    }

    function setSelection(values) {
        selected = values.slice().sort(function (a, b) { return a - b; });
        syncInputs();
    }

    function showRangeModal(from, to, onConfirm) {
        pendingRange = { from: from, to: to, onConfirm: onConfirm };
        const start = Math.min(from, to);
        const end = Math.max(from, to);
        document.getElementById('jp-range-question').textContent =
            'Gunakan JP' + start + ' s.d. JP' + end + '?';
        $('#jpRangeModal').modal('show');
    }

    document.getElementById('jp-range-yes').addEventListener('click', function () {
        if (pendingRange) {
            setSelection(rangeValues(pendingRange.from, pendingRange.to));
            anchorJp = null;
            pendingRange = null;
        }
        $('#jpRangeModal').modal('hide');
    });

    document.getElementById('jp-range-no').addEventListener('click', function () {
        if (pendingRange) {
            pendingRange.onConfirm();
            pendingRange = null;
        }
        $('#jpRangeModal').modal('hide');
    });

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            const jp = parseInt(button.dataset.jp, 10);

            if (selected.length === 0) {
                setSelection([jp]);
                anchorJp = jp;
                return;
            }

            if (selected.length === 1 && selected[0] === jp) {
                setSelection([]);
                anchorJp = null;
                return;
            }

            if (anchorJp !== null && anchorJp !== jp) {
                const start = Math.min(anchorJp, jp);
                const end = Math.max(anchorJp, jp);
                if (end - start >= 1) {
                    showRangeModal(anchorJp, jp, function () {
                        const next = selected.includes(jp)
                            ? selected.filter(function (value) { return value !== jp; })
                            : selected.concat([jp]).sort(function (a, b) { return a - b; });
                        if (isConsecutive(next)) {
                            setSelection(next);
                        } else {
                            alert('Jam Pelajaran harus berurutan.');
                            setSelection([jp]);
                        }
                        anchorJp = jp;
                    });
                    return;
                }
            }

            const next = selected.includes(jp)
                ? selected.filter(function (value) { return value !== jp; })
                : selected.concat([jp]).sort(function (a, b) { return a - b; });

            if (!isConsecutive(next)) {
                alert('Jam Pelajaran harus berurutan.');
                setSelection([jp]);
                anchorJp = jp;
                return;
            }

            setSelection(next);
            anchorJp = jp;
        });
    });

    if (form) {
        form.addEventListener('submit', function (event) {
            if (selected.length === 0) {
                event.preventDefault();
                alert('Pilih minimal satu Jam Pelajaran.');
                return;
            }
            if (!isConsecutive(selected)) {
                event.preventDefault();
                alert('Jam Pelajaran harus berurutan.');
            }
        });
    }

    syncInputs();
})();
</script>
@endpush
