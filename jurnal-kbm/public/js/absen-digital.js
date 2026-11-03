(function () {
    function onReady(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    function extractKode(raw) {
        var text = (raw || '').trim();
        if (!text) {
            return '';
        }
        try {
            var url = new URL(text);
            var fromQuery = url.searchParams.get('kode');
            if (fromQuery) {
                return fromQuery;
            }
        } catch (e) {
            /* bukan URL */
        }
        var match = text.match(/(?:^|[?&])kode=([A-Za-z0-9]+)/);
        return match ? match[1] : text;
    }

    function initJenisFilter() {
        var jenis = document.getElementById('jenis-filter');
        var kegiatan = document.getElementById('kegiatan-id');
        if (!jenis || !kegiatan) {
            return;
        }
        var apply = function () {
            var want = jenis.value;
            var first = '';
            Array.prototype.forEach.call(kegiatan.options, function (opt) {
                if (!opt.value) {
                    return;
                }
                var show = !want || opt.getAttribute('data-jenis') === want;
                opt.hidden = !show;
                opt.disabled = !show;
                if (show && !first) {
                    first = opt.value;
                }
            });
            var selected = kegiatan.options[kegiatan.selectedIndex];
            if (!selected || selected.disabled) {
                kegiatan.value = first;
            }
        };
        jenis.addEventListener('change', apply);
        apply();
    }

    function initModePeserta() {
        var radios = document.querySelectorAll('input[name="mode_peserta"]');
        var userBox = document.getElementById('box-user');
        var nikBox = document.getElementById('box-nik');
        if (!radios.length || !userBox || !nikBox) {
            return;
        }
        var sync = function () {
            var selected = document.querySelector('input[name="mode_peserta"]:checked');
            var mode = selected ? selected.value : 'user';
            userBox.hidden = mode !== 'user';
            nikBox.hidden = mode !== 'nik';
            userBox.querySelectorAll('input, select').forEach(function (el) {
                el.disabled = mode !== 'user';
            });
            nikBox.querySelectorAll('input, select').forEach(function (el) {
                el.disabled = mode !== 'nik';
                if (el.name === 'nik' || el.name === 'nama') {
                    el.required = mode === 'nik';
                }
            });
        };
        radios.forEach(function (radio) {
            radio.addEventListener('change', sync);
        });
        sync();
    }

    function initLokasi() {
        var button = document.getElementById('btn-lokasi');
        var lat = document.getElementById('latitude');
        var lng = document.getElementById('longitude');
        var status = document.getElementById('lokasi-status');
        var tampil = document.getElementById('lokasi-tampil');
        if (!button || !lat || !lng) {
            return;
        }
        var render = function (sumber) {
            if (lat.value === '' || lng.value === '') {
                tampil.textContent = '';
                return;
            }
            tampil.textContent = 'Titik yang akan disimpan (' + sumber + '): ' + lat.value + ', ' + lng.value;
        };
        lat.addEventListener('input', function () { render('isian manual'); });
        lng.addEventListener('input', function () { render('isian manual'); });
        button.addEventListener('click', function () {
            if (!navigator.geolocation) {
                status.textContent = 'GPS tidak tersedia di peramban ini. Isi lintang dan bujur secara manual.';
                return;
            }
            status.textContent = 'Mengambil lokasi...';
            navigator.geolocation.getCurrentPosition(function (pos) {
                lat.value = Number(pos.coords.latitude).toFixed(6);
                lng.value = Number(pos.coords.longitude).toFixed(6);
                status.textContent = 'Lokasi GPS berhasil diambil.';
                render('GPS');
            }, function () {
                status.textContent = 'Lokasi ditolak atau gagal. Isi lintang dan bujur secara manual.';
            }, { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 });
        });
        render('isian');
    }

    function initTtd() {
        var canvas = document.getElementById('ttd-canvas');
        var input = document.getElementById('ttd-input');
        var clear = document.getElementById('ttd-clear');
        var error = document.getElementById('ttd-error');
        var form = document.getElementById('form-absen');
        if (!canvas || !input || !form) {
            return;
        }
        var ctx = canvas.getContext('2d');
        var dirty = false;
        var drawing = false;
        var ratio = 1;

        function paintBackground() {
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width / ratio, canvas.height / ratio);
            ctx.strokeStyle = '#111';
            ctx.lineWidth = 2.6;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        function resize() {
            ratio = Math.max(window.devicePixelRatio || 1, 1);
            var rect = canvas.getBoundingClientRect();
            var width = rect.width || canvas.parentElement.clientWidth || 320;
            canvas.width = Math.round(width * ratio);
            canvas.height = Math.round(220 * ratio);
            paintBackground();
            dirty = false;
        }

        resize();

        function point(event) {
            var rect = canvas.getBoundingClientRect();
            return { x: event.clientX - rect.left, y: event.clientY - rect.top };
        }

        canvas.addEventListener('pointerdown', function (event) {
            canvas.setPointerCapture(event.pointerId);
            drawing = true;
            var p = point(event);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            event.preventDefault();
        });
        canvas.addEventListener('pointermove', function (event) {
            if (!drawing) {
                return;
            }
            var p = point(event);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            dirty = true;
            event.preventDefault();
        });
        function stop() { drawing = false; }
        canvas.addEventListener('pointerup', stop);
        canvas.addEventListener('pointercancel', stop);

        if (clear) {
            clear.addEventListener('click', function () {
                paintBackground();
                dirty = false;
                input.value = '';
                if (error) {
                    error.textContent = '';
                }
            });
        }

        form.addEventListener('submit', function (event) {
            if (!dirty) {
                event.preventDefault();
                if (error) {
                    error.textContent = 'Tanda tangan masih kosong.';
                }
                return;
            }
            input.value = canvas.toDataURL('image/png');
        });
    }

    function applyScan(raw) {
        var input = document.getElementById('kode-input');
        var form = document.getElementById('form-cari-kode');
        if (!input || !form) {
            return;
        }
        input.value = extractKode(raw);
        form.submit();
    }

    function initScanner() {
        var button = document.getElementById('btn-kamera');
        var video = document.getElementById('scan-video');
        var status = document.getElementById('scan-status');
        var file = document.getElementById('scan-file');
        if (!button || !video) {
            return;
        }
        var stream = null;
        var stopped = false;

        function note(text) {
            if (status) {
                status.textContent = text;
            }
        }

        button.addEventListener('click', function () {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                note('Kamera tidak tersedia. Ketik kode atau tempel hasil scan.');
                return;
            }
            if (!('BarcodeDetector' in window)) {
                note('Pemindaian langsung tidak didukung peramban ini. Ketik kode atau tempel hasil scan. Gambar QR tetap bisa dicoba jika peramban mendukung.');
            }
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
                .then(function (media) {
                    stream = media;
                    stopped = false;
                    video.srcObject = media;
                    video.classList.add('is-on');
                    return video.play();
                })
                .then(function () {
                    if (!('BarcodeDetector' in window)) {
                        note('Kamera terbuka, tetapi pembaca QR tidak ada. Gunakan isian kode di bawah.');
                        return;
                    }
                    var detector = new BarcodeDetector({ formats: ['qr_code'] });
                    var tick = function () {
                        if (stopped) {
                            return;
                        }
                        detector.detect(video).then(function (codes) {
                            if (codes && codes.length) {
                                stopped = true;
                                if (stream) {
                                    stream.getTracks().forEach(function (track) { track.stop(); });
                                }
                                applyScan(codes[0].rawValue);
                                return;
                            }
                            requestAnimationFrame(tick);
                        }).catch(function () {
                            note('Pembacaan kamera gagal. Ketik kode secara manual.');
                        });
                    };
                    note('Arahkan kamera ke QR.');
                    requestAnimationFrame(tick);
                })
                .catch(function () {
                    note('Kamera tidak bisa dibuka. Ketik kode atau tempel hasil scan.');
                });
        });

        if (file) {
            file.addEventListener('change', function () {
                var image = file.files && file.files[0];
                if (!image) {
                    return;
                }
                if (!('BarcodeDetector' in window) || !('createImageBitmap' in window)) {
                    note('Gambar QR tidak bisa dibaca di peramban ini. Ketik kode yang tertera di samping QR.');
                    return;
                }
                createImageBitmap(image).then(function (bitmap) {
                    var detector = new BarcodeDetector({ formats: ['qr_code'] });
                    return detector.detect(bitmap);
                }).then(function (codes) {
                    if (codes && codes.length) {
                        applyScan(codes[0].rawValue);
                        return;
                    }
                    note('QR pada gambar tidak terbaca. Ketik kodenya secara manual.');
                }).catch(function () {
                    note('Gambar tidak bisa dipindai. Ketik kode secara manual.');
                });
            });
        }
    }

    function initQr() {
        var box = document.getElementById('qrcode');
        if (!box || typeof QRCode === 'undefined') {
            return;
        }
        var text = box.getAttribute('data-text') || '';
        if (!text) {
            return;
        }
        new QRCode(box, {
            text: text,
            width: 220,
            height: 220,
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    onReady(function () {
        initJenisFilter();
        initModePeserta();
        initLokasi();
        initTtd();
        initScanner();
        initQr();
    });
})();
