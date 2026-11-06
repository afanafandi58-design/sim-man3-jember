(function () {
  var akar = document.getElementById('ujian');
  if (!akar) return;

  var basis = akar.getAttribute('data-basis') || '';
  var csrf = akar.getAttribute('data-csrf') || '';
  var kunciLokal = 'cbt-buffer-' + (akar.getAttribute('data-pengerjaan') || '');
  var DETAK_MS = 5000;
  var sisa = parseInt(akar.getAttribute('data-sisa') || '0', 10);
  var nomor = 1;
  var total = 1;
  var soal = null;
  var daftar = [];
  var daring = false;
  var sibuk = false;
  var sedang = false;
  var mulaiHitung = false;
  var tunda = null;

  function rute(nama) {
    return basis + '/index.php?r=' + nama;
  }

  function bacaBuffer() {
    try {
      var data = JSON.parse(localStorage.getItem(kunciLokal) || '{}');
      return data && typeof data === 'object' ? data : {};
    } catch (e) {
      return {};
    }
  }

  function tulisBuffer(data) {
    localStorage.setItem(kunciLokal, JSON.stringify(data));
  }

  function bacaRespons(res) {
    return res.json().catch(function () {
      throw new Error('server');
    }).then(function (data) {
      if (data && data.masuk === false) {
        window.location = rute('masuk');
        throw new Error('masuk');
      }
      if (data && data.status === 'selesai') {
        window.location = rute('selesai');
        throw new Error('selesai');
      }
      if (!res.ok || !data || !data.ok) {
        throw new Error('server');
      }
      return data;
    });
  }

  function pos(nama, tubuh) {
    return fetch(rute(nama), {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-Token': csrf
      },
      body: JSON.stringify(tubuh || {})
    }).then(bacaRespons);
  }

  function ambil(nama, query) {
    return fetch(rute(nama) + (query || ''), {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    }).then(bacaRespons);
  }

  function gambarWaktu() {
    var detik = Math.max(0, sisa);
    var jam = Math.floor(detik / 3600);
    var menit = Math.floor((detik % 3600) / 60);
    var d = detik % 60;
    var dua = function (n) { return (n < 10 ? '0' : '') + n; };
    document.getElementById('waktu').textContent = jam > 0
      ? jam + ':' + dua(menit) + ':' + dua(d)
      : dua(menit) + ':' + dua(d);
  }

  function spanduk(teks) {
    var el = document.getElementById('spanduk');
    el.hidden = !teks;
    el.textContent = teks || '';
  }

  function pesanPutus() {
    return 'Koneksi terputus atau server bermasalah. Waktu dijeda. Jawaban yang sudah tersimpan tetap di server. Jawaban yang belum terkirim disimpan di komputer ini dan dikirim saat tersambung.';
  }

  function terapkan(data) {
    if (typeof data.sisa_detik === 'number') {
      sisa = data.sisa_detik;
      gambarWaktu();
    }
    return false;
  }

  function isiTerisi(isi) {
    if (!isi) return false;
    if (typeof isi.pilihan === 'string') return isi.pilihan !== '';
    if (Array.isArray(isi.pilihan)) return isi.pilihan.length > 0;
    if (isi.pasangan) {
      var kunci = Object.keys(isi.pasangan);
      for (var i = 0; i < kunci.length; i++) {
        if (String(isi.pasangan[kunci[i]] || '') !== '') return true;
      }
    }
    return false;
  }

  function kumpulkanForm() {
    if (!soal) return null;
    var isi = {};
    if (soal.tipe === 'pg') {
      var radio = document.querySelector('input[name="pilihan"]:checked');
      isi = { pilihan: radio ? radio.value : '' };
    } else if (soal.tipe === 'pgk') {
      var nilai = [];
      document.querySelectorAll('input[name="pilihan"]:checked').forEach(function (el) {
        nilai.push(el.value);
      });
      isi = { pilihan: nilai };
    } else {
      var pasangan = {};
      document.querySelectorAll('[data-kiri]').forEach(function (sel) {
        pasangan[sel.getAttribute('data-kiri')] = sel.value;
      });
      isi = { pasangan: pasangan };
    }
    return { soal_id: soal.id, isi: isi, ragu: soal.ragu ? 1 : 0 };
  }

  function taruhBuffer() {
    var item = kumpulkanForm();
    if (!item) return;
    var semua = bacaBuffer();
    semua[String(item.soal_id)] = item;
    tulisBuffer(semua);
    gambarDaftar();
  }

  function kirimBuffer() {
    var semua = bacaBuffer();
    var daftarItem = Object.keys(semua).map(function (k) { return semua[k]; });
    if (!daftarItem.length) return Promise.resolve();
    return pos('api/jawaban', { jawaban: daftarItem }).then(function () {
      var kini = bacaBuffer();
      daftarItem.forEach(function (item) {
        var ada = kini[String(item.soal_id)];
        if (ada && JSON.stringify(ada) === JSON.stringify(item)) {
          delete kini[String(item.soal_id)];
        }
      });
      tulisBuffer(kini);
    });
  }

  function putus() {
    daring = false;
    spanduk(pesanPutus());
    pos('api/jeda', {}).catch(function () {});
  }

  function muat(ke) {
    if (sibuk) return Promise.resolve();
    sibuk = true;
    taruhBuffer();
    return kirimBuffer().then(function () {
      return ambil('api/soal', '&nomor=' + encodeURIComponent(ke));
    }).then(function (data) {
      soal = data;
      nomor = data.nomor;
      total = data.total;
      daftar = data.daftar || [];
      if (typeof data.sisa_detik === 'number') {
        sisa = data.sisa_detik;
        gambarWaktu();
      }
      gambar();
      sibuk = false;
    }).catch(function (err) {
      sibuk = false;
      if (!err || err.message !== 'selesai') putus();
    });
  }

  function el(tag, cls, text) {
    var node = document.createElement(tag);
    if (cls) node.className = cls;
    if (text != null) node.textContent = text;
    return node;
  }

  function labelTipe(tipe) {
    if (tipe === 'pgk') return 'Pilihan ganda kompleks';
    if (tipe === 'menjodohkan') return 'Menjodohkan';
    return 'Pilihan ganda';
  }

  function gambar() {
    var buf = bacaBuffer()[String(soal.id)];
    var isi = buf ? (buf.isi || {}) : (soal.jawaban || {});
    if (buf) soal.ragu = buf.ragu ? 1 : 0;
    var kotak = document.getElementById('soal');
    kotak.textContent = '';
    kotak.appendChild(el('p', 'nomor-soal', 'Soal ' + nomor + ' dari ' + total));
    kotak.appendChild(el('p', 'redup', labelTipe(soal.tipe)));
    kotak.appendChild(el('div', 'pertanyaan', soal.pertanyaan));
    if (soal.audio) {
      var audio = document.createElement('audio');
      audio.controls = true;
      audio.preload = 'none';
      audio.src = basis + '/unggah/audio/' + soal.audio;
      kotak.appendChild(audio);
    } else if (soal.audio_hilang) {
      kotak.appendChild(el('p', 'peringatan', 'Berkas audio belum tersedia di server.'));
    }
    if (soal.tipe === 'menjodohkan') {
      (soal.kiri || []).forEach(function (kiri) {
        var baris = el('div', 'jodoh');
        baris.appendChild(el('span', '', kiri.kode + '. ' + kiri.teks));
        var select = document.createElement('select');
        select.setAttribute('data-kiri', kiri.kode);
        select.appendChild(new Option('Pilih pasangan', ''));
        (soal.kanan || []).forEach(function (kanan) {
          select.appendChild(new Option(kanan.kode + '. ' + kanan.teks, kanan.kode));
        });
        select.value = isi.pasangan && isi.pasangan[kiri.kode] ? isi.pasangan[kiri.kode] : '';
        select.addEventListener('change', function () {
          taruhBuffer();
          jadwalSimpan();
        });
        baris.appendChild(select);
        kotak.appendChild(baris);
      });
    } else {
      (soal.pilihan || []).forEach(function (pilihan) {
        var label = el('label', 'opsi');
        var input = document.createElement('input');
        input.type = soal.tipe === 'pgk' ? 'checkbox' : 'radio';
        input.name = 'pilihan';
        input.value = pilihan.kode;
        if (soal.tipe === 'pgk') {
          var arr = Array.isArray(isi.pilihan) ? isi.pilihan : [];
          input.checked = arr.indexOf(pilihan.kode) !== -1;
        } else {
          input.checked = isi.pilihan === pilihan.kode;
        }
        input.addEventListener('change', function () {
          taruhBuffer();
          jadwalSimpan();
        });
        label.appendChild(input);
        label.appendChild(el('span', 'kode-opsi', pilihan.kode));
        label.appendChild(el('span', '', pilihan.teks));
        kotak.appendChild(label);
      });
    }
    var ragu = document.getElementById('btn-ragu');
    ragu.classList.toggle('sedang-ragu', !!soal.ragu);
    ragu.setAttribute('aria-pressed', soal.ragu ? 'true' : 'false');
    document.getElementById('btn-kembali').disabled = nomor <= 1;
    document.getElementById('btn-lanjut').disabled = nomor >= total;
    gambarDaftar();
  }

  function gambarDaftar() {
    var kotak = document.getElementById('daftar');
    if (!kotak) return;
    kotak.textContent = '';
    kotak.appendChild(el('p', 'judul-daftar', 'Daftar soal'));
    var buf = bacaBuffer();
    daftar.forEach(function (item) {
      var ragu = item.ragu;
      var terjawab = item.terjawab;
      var lokal = buf[String(item.id)];
      if (lokal) {
        ragu = lokal.ragu ? 1 : 0;
        terjawab = isiTerisi(lokal.isi) ? 1 : 0;
      }
      var tombol = document.createElement('button');
      tombol.type = 'button';
      tombol.textContent = String(item.nomor);
      tombol.className = 'nomor';
      if (item.nomor === nomor) tombol.classList.add('aktif');
      if (terjawab) tombol.classList.add('terjawab');
      if (ragu) tombol.classList.add('ragu');
      tombol.addEventListener('click', function () { muat(item.nomor); });
      kotak.appendChild(tombol);
    });
  }

  function jadwalSimpan() {
    clearTimeout(tunda);
    tunda = setTimeout(function () {
      if (!daring) return;
      kirimBuffer().catch(function () { putus(); });
    }, 2000);
  }

  function detik() {
    if (!mulaiHitung || !daring) return;
    if (sisa <= 0) {
      kumpulkan(true);
      return;
    }
    sisa -= 1;
    gambarWaktu();
    if (sisa <= 0) kumpulkan(true);
  }

  function cobaSambung() {
    if (sedang) return;
    sedang = true;
    spanduk('Mencoba menyambungkan kembali. Waktu tetap dijeda.');
    pos('api/lanjut', {}).then(function (data) {
      daring = true;
      spanduk('');
      terapkan(data);
      return kirimBuffer();
    }).then(function () {
      if (daring && !soal) return muat(nomor || 1);
    }).catch(function (err) {
      if (!err || err.message !== 'selesai') {
        daring = false;
        spanduk(pesanPutus());
      }
    }).then(function () {
      sedang = false;
    });
  }

  function jaringan() {
    if (sedang || sibuk) return;
    if (!daring) {
      cobaSambung();
      return;
    }
    sedang = true;
    pos('api/detak', {}).then(function (data) {
      terapkan(data);
      if (data.status === 'jeda') {
        daring = false;
        spanduk('Waktu dijeda. Menyambungkan kembali.');
      }
    }).catch(function (err) {
      if (!err || err.message !== 'selesai') putus();
    }).then(function () {
      sedang = false;
    });
  }

  function kumpulkan(paksa) {
    if (sibuk) return;
    if (!paksa && !window.confirm('Kumpulkan jawaban dan akhiri ujian?')) return;
    sibuk = true;
    taruhBuffer();
    kirimBuffer().then(function () {
      return pos('api/selesai', {});
    }).then(function () {
      localStorage.removeItem(kunciLokal);
      window.location = rute('selesai');
    }).catch(function (err) {
      sibuk = false;
      if (!err || err.message !== 'selesai') putus();
    });
  }

  document.getElementById('btn-lanjut').addEventListener('click', function () {
    if (nomor < total) muat(nomor + 1);
  });
  document.getElementById('btn-kembali').addEventListener('click', function () {
    if (nomor > 1) muat(nomor - 1);
  });
  document.getElementById('btn-ragu').addEventListener('click', function () {
    if (!soal) return;
    soal.ragu = soal.ragu ? 0 : 1;
    document.getElementById('btn-ragu').classList.toggle('sedang-ragu', !!soal.ragu);
    taruhBuffer();
    jadwalSimpan();
  });
  document.getElementById('btn-kumpulkan').addEventListener('click', function () { kumpulkan(false); });
  document.getElementById('btn-keluar').addEventListener('click', function () {
    if (!window.confirm('Keluar dari layar ujian? Waktu dijeda. Jawaban yang sudah tersimpan tetap ada.')) return;
    taruhBuffer();
    var kirim = daring ? kirimBuffer() : Promise.resolve();
    kirim.finally(function () {
      localStorage.getItem(kunciLokal);
      document.getElementById('form-keluar').submit();
    });
  });

  window.addEventListener('beforeunload', function (e) {
    if (Object.keys(bacaBuffer()).length) {
      e.preventDefault();
      e.returnValue = '';
    }
  });

  gambarWaktu();
  setInterval(detik, 1000);
  setInterval(jaringan, DETAK_MS);
  pos('api/lanjut', {}).then(function (data) {
    daring = true;
    mulaiHitung = true;
    terapkan(data);
    return kirimBuffer().then(function () { return muat(1); });
  }).catch(function (err) {
    mulaiHitung = true;
    if (!err || err.message !== 'selesai') {
      daring = false;
      spanduk(pesanPutus());
    }
  });
})();
