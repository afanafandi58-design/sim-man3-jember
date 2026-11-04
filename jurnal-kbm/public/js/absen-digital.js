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
        var petaEl = document.getElementById('peta-lokasi');
        var button = document.getElementById('btn-lokasi');
        var lat = document.getElementById('latitude');
        var lng = document.getElementById('longitude');
        var status = document.getElementById('lokasi-status');
        if (!petaEl || !lat || !lng) {
            return;
        }
        if (typeof L === 'undefined') {
            if (status) {
                status.textContent = 'Peta online belum bisa dimuat. Periksa koneksi, lalu muat ulang halaman.';
            }
            return;
        }
        function tulis(text) {
            if (status) {
                status.textContent = text;
            }
        }
        var ada = lat.value !== '' && lng.value !== '' && !isNaN(parseFloat(lat.value)) && !isNaN(parseFloat(lng.value));
        var awal = ada ? [parseFloat(lat.value), parseFloat(lng.value)] : [-8.173, 113.7];
        var map = L.map(petaEl).setView(awal, ada ? 17 : 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        var marker = null;
        var circle = null;
        var icon = L.divIcon({ className: 'pin-absen', iconSize: [22, 22], iconAnchor: [11, 11] });
        function pasang(la, ln, sumber) {
            var nomorLat = Number(la);
            var nomorLng = Number(ln);
            lat.value = nomorLat.toFixed(6);
            lng.value = nomorLng.toFixed(6);
            if (!marker) {
                marker = L.marker([nomorLat, nomorLng], { draggable: true, icon: icon }).addTo(map);
                marker.on('dragend', function () {
                    var titik = marker.getLatLng();
                    pasang(titik.lat, titik.lng, 'geser pin');
                });
            } else if (sumber !== 'geser pin') {
                marker.setLatLng([nomorLat, nomorLng]);
            }
            if (!circle) {
                circle = L.circle([nomorLat, nomorLng], { radius: 75, color: '#2e7d32', weight: 2, fillColor: '#2e7d32', fillOpacity: 0.15 }).addTo(map);
            } else {
                circle.setLatLng([nomorLat, nomorLng]);
            }
            tulis('Koordinat terisi dari ' + sumber + '. Radius absen 75 meter.');
        }
        map.on('click', function (event) {
            pasang(event.latlng.lat, event.latlng.lng, 'klik peta');
        });
        if (ada) {
            pasang(awal[0], awal[1], 'data tersimpan');
        }
        if (button) {
            button.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    tulis('GPS tidak tersedia di peramban ini. Klik peta atau geser pin.');
                    return;
                }
                tulis('Mengambil lokasi GPS...');
                navigator.geolocation.getCurrentPosition(function (pos) {
                    pasang(pos.coords.latitude, pos.coords.longitude, 'GPS');
                    map.setView([pos.coords.latitude, pos.coords.longitude], 17);
                }, function () {
                    tulis('GPS ditolak atau gagal. Klik peta atau geser pin.');
                }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
            });
        }
        setTimeout(function () { map.invalidateSize(); }, 250);
    }

    function initLokasiPeserta() {
        var form = document.getElementById('form-absen-scan');
        var lat = document.getElementById('latitude');
        var lng = document.getElementById('longitude');
        var status = document.getElementById('lokasi-status');
        if (!form || !lat || !lng || lat.type !== 'hidden') {
            return;
        }
        function tulis(text) {
            if (status) {
                status.textContent = text;
            }
        }
        function ambil() {
            return new Promise(function (resolve, reject) {
                if (!navigator.geolocation) {
                    tulis('GPS tidak tersedia di peramban ini. Absen tidak bisa disimpan.');
                    reject();
                    return;
                }
                tulis('Membaca lokasi perangkat...');
                navigator.geolocation.getCurrentPosition(function (pos) {
                    lat.value = Number(pos.coords.latitude).toFixed(6);
                    lng.value = Number(pos.coords.longitude).toFixed(6);
                    tulis('Lokasi terbaca. Absen hanya berlaku dalam radius 75 meter dari titik kegiatan.');
                    resolve();
                }, function () {
                    lat.value = '';
                    lng.value = '';
                    tulis('Lokasi perangkat belum terbaca. Izinkan GPS, lalu ketuk Simpan lagi.');
                    reject();
                }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
            });
        }
        ambil().catch(function () {});
        form.addEventListener('submit', function (event) {
            if (lat.value !== '' && lng.value !== '') {
                return;
            }
            event.preventDefault();
            ambil().then(function () {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                }
            }).catch(function () {});
        }, true);
    }

    function initTtd() {
        var canvas = document.getElementById('ttd-canvas');
        var input = document.getElementById('ttd-input');
        var clear = document.getElementById('ttd-clear');
        var error = document.getElementById('ttd-error');
        var box = document.getElementById('ttd-kotak') || canvas;
        var form = document.getElementById('form-absen-scan') || document.getElementById('form-absen');
        if (!canvas || !input || !form) {
            return;
        }
        var ctx = canvas.getContext('2d');
        var dirty = false;
        var drawing = false;
        var activeId = null;
        var fitted = false;
        canvas.style.touchAction = 'none';
        if (box && box.style) {
            box.style.touchAction = 'none';
        }

        function listen(el, name, fn) {
            try {
                el.addEventListener(name, fn, { passive: false });
            } catch (err) {
                el.addEventListener(name, fn, false);
            }
        }

        function paintBackground() {
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.strokeStyle = '#111';
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.lineWidth = Math.max(4, Math.round((canvas.width || 300) / 90));
        }

        function fit() {
            if (dirty) {
                return;
            }
            var rect = canvas.getBoundingClientRect();
            if (rect.width < 20 || rect.height < 20) {
                return;
            }
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = Math.round(rect.width * ratio);
            canvas.height = Math.round(rect.height * ratio);
            paintBackground();
            fitted = true;
        }

        function arm() {
            fit();
            if (!fitted) {
                setTimeout(fit, 60);
                setTimeout(fit, 280);
            }
        }
        arm();
        document.addEventListener('ttd-buka', arm);
        var pembuka = document.getElementById('btn-kegiatan');
        if (pembuka) {
            pembuka.addEventListener('click', function () {
                setTimeout(arm, 0);
            });
        }

        function pointFrom(clientX, clientY) {
            var rect = canvas.getBoundingClientRect();
            var scaleX = rect.width ? canvas.width / rect.width : 1;
            var scaleY = rect.height ? canvas.height / rect.height : 1;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        }

        function touchPoint(event) {
            var list = event.touches && event.touches.length ? event.touches : event.changedTouches;
            if (!list || !list.length) {
                return null;
            }
            var i;
            var touch = list[0];
            if (activeId !== null) {
                for (i = 0; i < list.length; i++) {
                    if (list[i].identifier === activeId) {
                        touch = list[i];
                        break;
                    }
                }
            }
            return pointFrom(touch.clientX, touch.clientY);
        }

        function start(p) {
            if (!p) {
                return;
            }
            if (!fitted) {
                fit();
            }
            drawing = true;
            dirty = true;
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            ctx.lineTo(p.x + 0.01, p.y + 0.01);
            ctx.stroke();
        }

        function move(p) {
            if (!drawing || !p) {
                return;
            }
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            dirty = true;
        }

        function stop() {
            drawing = false;
            activeId = null;
        }

        function block(event) {
            if (event.cancelable) {
                event.preventDefault();
            }
        }

        listen(canvas, 'touchstart', function (event) {
            block(event);
            if (event.changedTouches && event.changedTouches[0]) {
                activeId = event.changedTouches[0].identifier;
            }
            start(touchPoint(event));
        });
        listen(canvas, 'touchmove', function (event) {
            block(event);
            move(touchPoint(event));
        });
        listen(box, 'touchmove', function (event) {
            if (drawing) {
                block(event);
            }
        });
        listen(canvas, 'touchend', function (event) {
            block(event);
            move(touchPoint(event));
            stop();
        });
        listen(canvas, 'touchcancel', function (event) {
            block(event);
            stop();
        });

        canvas.addEventListener('mousedown', function (event) {
            if (event.button !== 0) {
                return;
            }
            event.preventDefault();
            start(pointFrom(event.clientX, event.clientY));
        });
        window.addEventListener('mousemove', function (event) {
            if (!drawing || activeId !== null) {
                return;
            }
            move(pointFrom(event.clientX, event.clientY));
        });
        window.addEventListener('mouseup', function () {
            if (activeId === null) {
                stop();
            }
        });

        if (clear) {
            clear.addEventListener('click', function () {
                dirty = false;
                input.value = '';
                paintBackground();
                if (error) {
                    error.textContent = '';
                }
            });
        }

        form.addEventListener('submit', function (event) {
            if (!dirty) {
                event.preventDefault();
                if (error) {
                    error.textContent = 'Tanda tangan masih kosong. Gambar dengan jari di kotak.';
                }
                return;
            }
            try {
                input.value = canvas.toDataURL('image/png');
            } catch (err) {
                event.preventDefault();
                if (error) {
                    error.textContent = 'Tanda tangan tidak terbaca. Gambar ulang di kotak.';
                }
            }
        }, true);
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

    function initFoto() {
        var input = document.getElementById('foto');
        var form = document.getElementById('form-absen-scan');
        var status = document.getElementById('foto-status');
        var preview = document.getElementById('foto-pratinjau');
        if (!input || !form || input.getAttribute('data-perkecil') === '1') {
            return;
        }
        input.setAttribute('data-perkecil', '1');
        input.required = false;
        var batas = 1024 * 1024;
        var fotoSiap = false;
        var sedang = false;
        var menungguKirim = false;
        var memasang = false;
        var generasi = 0;
        var pratinjauUrl = '';

        function tulis(text) {
            if (status) {
                status.textContent = text;
            }
        }

        function tampilkan(blob) {
            if (!preview || !window.URL || !URL.createObjectURL) {
                return;
            }
            if (pratinjauUrl) {
                URL.revokeObjectURL(pratinjauUrl);
            }
            pratinjauUrl = URL.createObjectURL(blob);
            preview.src = pratinjauUrl;
            preview.hidden = false;
        }

        function pasang(blob) {
            var hasil = new File([blob], 'selfie.jpg', { type: 'image/jpeg', lastModified: Date.now() });
            var dt = new DataTransfer();
            dt.items.add(hasil);
            memasang = true;
            input.files = dt.files;
            memasang = false;
            tampilkan(blob);
        }

        function muat(file) {
            if (window.createImageBitmap) {
                return createImageBitmap(file, { imageOrientation: 'from-image' }).catch(function () {
                    return createImageBitmap(file);
                });
            }
            return new Promise(function (resolve, reject) {
                var url = URL.createObjectURL(file);
                var img = new Image();
                img.onload = function () {
                    URL.revokeObjectURL(url);
                    resolve(img);
                };
                img.onerror = function () {
                    URL.revokeObjectURL(url);
                    reject(new Error('baca'));
                };
                img.src = url;
            });
        }

        function jadiBlob(source, width, height, quality) {
            var canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            var ctx = canvas.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, width, height);
            ctx.drawImage(source, 0, 0, width, height);
            return new Promise(function (resolve, reject) {
                if (!canvas.toBlob) {
                    reject(new Error('blob'));
                    return;
                }
                canvas.toBlob(function (blob) {
                    if (!blob) {
                        reject(new Error('blob'));
                        return;
                    }
                    resolve(blob);
                }, 'image/jpeg', quality);
            });
        }

        function perkecil(file) {
            return muat(file).then(function (source) {
                var sw = source.width || source.naturalWidth || 0;
                var sh = source.height || source.naturalHeight || 0;
                if (!sw || !sh) {
                    if (source.close) {
                        source.close();
                    }
                    throw new Error('ukuran');
                }
                var side = 1600;
                var quality = 0.82;
                function coba() {
                    var scale = Math.min(1, side / Math.max(sw, sh));
                    var width = Math.max(1, Math.round(sw * scale));
                    var height = Math.max(1, Math.round(sh * scale));
                    return jadiBlob(source, width, height, quality).then(function (blob) {
                        if (blob.size < batas) {
                            if (source.close) {
                                source.close();
                            }
                            return blob;
                        }
                        if (quality > 0.5) {
                            quality = Math.round((quality - 0.12) * 100) / 100;
                            return coba();
                        }
                        if (side > 640) {
                            side = Math.round(side * 0.75);
                            quality = 0.7;
                            return coba();
                        }
                        if (source.close) {
                            source.close();
                        }
                        throw new Error('besar');
                    });
                }
                return coba();
            });
        }

        function proses(file) {
            generasi += 1;
            var saya = generasi;
            fotoSiap = false;
            sedang = true;
            input.value = '';
            tulis('Foto sedang diperkecil di perangkat...');
            perkecil(file).then(function (blob) {
                if (saya !== generasi) {
                    return;
                }
                if (!blob || blob.size >= batas || typeof DataTransfer === 'undefined') {
                    throw new Error('besar');
                }
                pasang(blob);
                var terpasang = input.files && input.files[0];
                if (!terpasang || terpasang.size >= batas || terpasang.type !== 'image/jpeg') {
                    throw new Error('pasang');
                }
                fotoSiap = true;
                sedang = false;
                tulis('Foto siap diunggah (' + Math.max(1, Math.round(blob.size / 1024)) + ' KB, di bawah 1 MB).');
                if (menungguKirim && typeof form.requestSubmit === 'function') {
                    menungguKirim = false;
                    form.requestSubmit();
                }
            }).catch(function () {
                if (saya !== generasi) {
                    return;
                }
                input.value = '';
                fotoSiap = false;
                sedang = false;
                menungguKirim = false;
                tulis('Foto tidak bisa diperkecil. Ambil ulang dengan kamera.');
            });
        }

        input.addEventListener('change', function () {
            if (memasang) {
                return;
            }
            var file = input.files && input.files[0];
            if (!file) {
                if (!sedang) {
                    fotoSiap = false;
                }
                return;
            }
            menungguKirim = false;
            proses(file);
        });

        form.addEventListener('submit', function (event) {
            var file = input.files && input.files[0];
            if (fotoSiap && file && file.size < batas && file.type === 'image/jpeg') {
                return;
            }
            event.preventDefault();
            if (sedang) {
                menungguKirim = true;
                tulis('Foto sedang diperkecil. Absen dikirim setelah foto siap, tanpa file asli yang besar.');
                return;
            }
            if (file && (file.size >= batas || file.type !== 'image/jpeg')) {
                menungguKirim = true;
                proses(file);
                return;
            }
            tulis('Pilih atau ambil foto selfie. File asli yang besar tidak dikirim.');
        }, true);
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
            width: 280,
            height: 280,
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    onReady(function () {
        initJenisFilter();
        initModePeserta();
        initLokasi();
        initLokasiPeserta();
        initTtd();
        initFoto();
        initScanner();
        initQr();
    });
})();
