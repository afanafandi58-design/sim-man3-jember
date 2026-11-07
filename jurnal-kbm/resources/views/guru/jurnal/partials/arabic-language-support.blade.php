{{-- Dukungan penulisan Bahasa Arab — Form Jurnal KBM (UI only) --}}
<div class="card card-outline card-info mb-3" id="jurnal-lang-switcher">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <strong class="mr-2">Bahasa Penulisan</strong>
                <div class="btn-group btn-group-sm" role="group" aria-label="Pilih bahasa penulisan">
                    <button type="button" class="btn btn-outline-primary jurnal-lang-btn active" data-lang="id">
                        Indonesia
                    </button>
                    <button type="button" class="btn btn-outline-success jurnal-lang-btn" data-lang="ar">
                        العربية
                    </button>
                </div>
            </div>
            <small class="text-muted mt-2 mt-md-0">
                Shortcut: <kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>A</kbd> Arabic ·
                <kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>I</kbd> Indonesia
            </small>
        </div>
    </div>
</div>

@push('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .jurnal-i18n-field.jurnal-lang-ar {
        direction: rtl;
        text-align: right;
        font-family: "Noto Naskh Arabic", serif;
        font-size: 18px;
        line-height: 1.8;
        unicode-bidi: plaintext;
    }
    .jurnal-i18n-field.jurnal-lang-id {
        direction: ltr;
        text-align: left;
        font-family: inherit;
        font-size: inherit;
        line-height: inherit;
        unicode-bidi: normal;
    }
    .jurnal-lang-btn.active[data-lang="id"] {
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }
    .jurnal-lang-btn.active[data-lang="ar"] {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }
</style>
@endpush

@push('js')
<script>
(function () {
    const STORAGE_KEY = 'sim_man3_jurnal_lang';
    const fields = document.querySelectorAll('.jurnal-i18n-field');
    const buttons = document.querySelectorAll('.jurnal-lang-btn');

    if (fields.length === 0) {
        return;
    }

    function applyLanguage(lang) {
        const isArabic = lang === 'ar';

        fields.forEach(function (field) {
            field.classList.toggle('jurnal-lang-ar', isArabic);
            field.classList.toggle('jurnal-lang-id', !isArabic);
            field.setAttribute('dir', isArabic ? 'rtl' : 'ltr');
            field.setAttribute('lang', isArabic ? 'ar' : 'id');
        });

        buttons.forEach(function (button) {
            const active = button.dataset.lang === lang;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        try {
            sessionStorage.setItem(STORAGE_KEY, lang);
        } catch (e) {
            /* ignore */
        }
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            applyLanguage(button.dataset.lang || 'id');
        });
    });

    document.addEventListener('keydown', function (event) {
        if (!event.ctrlKey || !event.shiftKey) {
            return;
        }

        const key = event.key.toLowerCase();

        if (key === 'a') {
            event.preventDefault();
            applyLanguage('ar');
        }

        if (key === 'i') {
            event.preventDefault();
            applyLanguage('id');
        }
    });

    let initial = 'id';
    try {
        initial = sessionStorage.getItem(STORAGE_KEY) || 'id';
    } catch (e) {
        initial = 'id';
    }

    applyLanguage(initial === 'ar' ? 'ar' : 'id');
})();
</script>
@endpush
