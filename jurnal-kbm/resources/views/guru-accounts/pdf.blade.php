<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Akun Guru</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h2 { margin-bottom: 4px; }
        .meta { margin-bottom: 16px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #e8f5e9; }
        .note { margin-top: 14px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <h2>Daftar Akun Guru SIM MAN 3 Jember</h2>
    <div class="meta">Tahun Pelajaran {{ $tahunPelajaran }} | Dicetak: {{ now()->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Guru</th>
                <th>Nama Guru</th>
                <th>Username</th>
                <th>Password Awal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($accounts as $index => $account)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $account['kode_guru'] }}</td>
                    <td>{{ $account['nama'] }}</td>
                    <td>{{ $account['username'] ?? '-' }}</td>
                    <td>{{ $account['password'] }}</td>
                    <td>{{ $account['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="note">
        Password hanya ditampilkan untuk akun yang baru dibuat atau di-reset pada sesi admin saat ini.
        Guru wajib segera mengganti password setelah login pertama.
    </p>
</body>
</html>
