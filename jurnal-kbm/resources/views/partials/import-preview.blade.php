<div id="{{ $previewId }}-panel" class="d-none mt-3">
    <div id="{{ $previewId }}-header-errors" class="alert alert-danger d-none"></div>
    <div class="alert alert-secondary mb-2">
        <strong>Hasil Validasi:</strong>
        <span id="{{ $previewId }}-summary"></span>
    </div>
    <div id="{{ $previewId }}-status" class="alert d-none mb-2"></div>
    <div id="{{ $previewId }}-errors" class="alert alert-warning d-none"></div>
    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
        <table class="table table-sm table-bordered">
            <thead id="{{ $previewId }}-head"></thead>
            <tbody id="{{ $previewId }}-body"></tbody>
        </table>
    </div>
</div>

@once
@push('js')
<script>
function setupImportPreview(config) {
    const btn = document.getElementById(config.buttonId);
    const fileInput = document.getElementById(config.fileId);
    const importBtn = config.importButtonId ? document.getElementById(config.importButtonId) : null;
    const panel = document.getElementById(config.previewId + '-panel');
    const summary = document.getElementById(config.previewId + '-summary');
    const statusBox = document.getElementById(config.previewId + '-status');
    const headerErrorsBox = document.getElementById(config.previewId + '-header-errors');
    const errorsBox = document.getElementById(config.previewId + '-errors');
    const head = document.getElementById(config.previewId + '-head');
    const body = document.getElementById(config.previewId + '-body');

    if (!btn || !fileInput) {
        return;
    }

    function setImportEnabled(enabled) {
        if (!importBtn) {
            return;
        }

        importBtn.disabled = !enabled;

        if (enabled) {
            importBtn.classList.remove('disabled');
            importBtn.title = '';
        } else {
            importBtn.title = 'Jalankan Preview terlebih dahulu hingga semua validasi berhasil.';
        }
    }

    setImportEnabled(false);

    fileInput.addEventListener('change', function () {
        panel.classList.add('d-none');
        setImportEnabled(false);
    });

    btn.addEventListener('click', function () {
        if (!fileInput.files.length) {
            alert('Pilih file Excel terlebih dahulu.');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memvalidasi...';
        setImportEnabled(false);

        fetch(config.previewUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(async (response) => {
            const contentType = response.headers.get('content-type') || '';

            if (! contentType.includes('application/json')) {
                const body = await response.text();
                console.error('Non-JSON preview response:', body.slice(0, 500));
                throw new Error(
                    'Server mengembalikan HTML, bukan JSON (HTTP ' + response.status + '). '
                    + 'Periksa storage/logs/laravel.log untuk detail error server.'
                );
            }

            const data = await response.json();

            if (!response.ok) {
                if (data.errors && data.errors.file) {
                    throw new Error(Array.isArray(data.errors.file) ? data.errors.file[0] : data.errors.file);
                }

                throw new Error(data.message || 'Validasi gagal.');
            }

            return data;
        })
        .then((data) => {
            panel.classList.remove('d-none');

            summary.textContent = data.total + ' baris — '
                + data.valid + ' valid, '
                + (data.duplicate || 0) + ' duplikat, '
                + data.invalid + ' error';

            if (data.header_errors && data.header_errors.length) {
                headerErrorsBox.classList.remove('d-none');
                headerErrorsBox.innerHTML = '<strong>Header / Sheet tidak valid:</strong><ul class="mb-0 mt-1"><li>'
                    + data.header_errors.join('</li><li>') + '</li></ul>';
            } else {
                headerErrorsBox.classList.add('d-none');
                headerErrorsBox.innerHTML = '';
            }

            if (data.can_import) {
                statusBox.className = 'alert alert-success mb-2';
                statusBox.textContent = 'Semua validasi berhasil. Tombol Import Data aktif.';
                statusBox.classList.remove('d-none');
            } else {
                statusBox.className = 'alert alert-danger mb-2';
                statusBox.textContent = 'Validasi belum lengkap. Perbaiki file lalu Preview ulang.';
                statusBox.classList.remove('d-none');
            }

            if (data.errors && data.errors.length) {
                errorsBox.classList.remove('d-none');
                errorsBox.innerHTML = '<strong>Detail error:</strong><ul class="mb-0 mt-1"><li>'
                    + data.errors.slice(0, 15).join('</li><li>') + '</li></ul>';

                if (data.errors.length > 15) {
                    errorsBox.innerHTML += '<small class="d-block mt-1">... dan ' + (data.errors.length - 15) + ' error lainnya</small>';
                }
            } else {
                errorsBox.classList.add('d-none');
                errorsBox.innerHTML = '';
            }

            const columns = config.columns || Object.keys(data.rows[0] || {}).filter(function (k) {
                return !['status', 'row', 'sheet'].includes(k);
            });

            head.innerHTML = '<tr><th>#</th>' + columns.map(function (c) { return '<th>' + c + '</th>'; }).join('') + '<th>Status</th></tr>';

            body.innerHTML = (data.rows || []).map(function (row) {
                const badge = row.status === 'error' ? 'danger'
                    : (row.status === 'duplicate' ? 'warning'
                    : (row.status === 'update' ? 'info' : 'success'));

                return '<tr><td>' + row.row + '</td>'
                    + columns.map(function (c) { return '<td>' + (row[c] ?? '') + '</td>'; }).join('')
                    + '<td><span class="badge badge-' + badge + '">' + row.status + '</span></td></tr>';
            }).join('');

            setImportEnabled(!!data.can_import);
        })
        .catch(function (error) {
            alert(error.message);
            setImportEnabled(false);
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-eye"></i> Preview & Validasi';
        });
    });
}
</script>
@endpush
@endonce
