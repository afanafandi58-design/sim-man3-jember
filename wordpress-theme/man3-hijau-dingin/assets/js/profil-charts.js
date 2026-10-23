(function () {
  var root = document.querySelector('[data-profil-charts]');
  if (!root || typeof Chart === 'undefined') return;

  var raw = root.getAttribute('data-profil-charts');
  var data;
  try {
    data = JSON.parse(raw);
  } catch (e) {
    return;
  }

  var colors = {
    green: ['#0f5244', '#1b856e', '#2a9f84', '#4db89a', '#7dcfb6', '#b4e6d5'],
    ink: '#0c1f1a',
    muted: '#3d5c52',
  };

  function labelsValues(rows) {
    rows = rows || [];
    return {
      labels: rows.map(function (r) { return r.label || ''; }),
      values: rows.map(function (r) { return Number(r.value) || 0; }),
    };
  }

  function doughnut(id, rows) {
    var el = document.getElementById(id);
    if (!el) return;
    var lv = labelsValues(rows);
    if (!lv.values.length) return;
    new Chart(el, {
      type: 'doughnut',
      data: {
        labels: lv.labels,
        datasets: [{
          data: lv.values,
          backgroundColor: colors.green.slice(0, lv.values.length),
          borderWidth: 0,
          hoverOffset: 6,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: colors.muted, boxWidth: 12, font: { family: 'Outfit, sans-serif', size: 12 } },
          },
        },
      },
    });
  }

  function bar(id, rows, horizontal) {
    var el = document.getElementById(id);
    if (!el) return;
    var lv = labelsValues(rows);
    if (!lv.values.length) return;
    new Chart(el, {
      type: 'bar',
      data: {
        labels: lv.labels,
        datasets: [{
          data: lv.values,
          backgroundColor: colors.green.slice(0, lv.values.length),
          borderRadius: 8,
          maxBarThickness: horizontal ? 28 : 48,
        }],
      },
      options: {
        indexAxis: horizontal ? 'y' : 'x',
        responsive: true,
        plugins: {
          legend: { display: false },
        },
        scales: {
          x: {
            ticks: { color: colors.muted, font: { family: 'Outfit, sans-serif' } },
            grid: { color: 'rgba(15, 82, 68, 0.08)' },
          },
          y: {
            ticks: { color: colors.muted, font: { family: 'Outfit, sans-serif' } },
            grid: { color: 'rgba(15, 82, 68, 0.08)' },
            beginAtZero: true,
          },
        },
      },
    });
  }

  doughnut('chart-siswa-gender', data.siswaGender);
  bar('chart-siswa-tingkat', data.siswaTingkat, false);
  bar('chart-siswa-peminatan', data.siswaPeminatan, true);
  doughnut('chart-pegawai-jenis', data.pegawaiJenis);
  doughnut('chart-pegawai-status', data.pegawaiStatus);
  bar('chart-pegawai-detail', data.pegawaiDetail, true);
  bar('chart-prasarana', data.prasaranaBars, false);
})();
