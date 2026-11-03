<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Siswa;

class SiswaController
{
    public function index(): void
    {
        require_login();
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $tingkat = isset($_GET['tingkat']) ? trim((string) $_GET['tingkat']) : '';
        $page = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
        $perPage = 30;
        $total = Siswa::count($q !== '' ? $q : null, $tingkat !== '' ? $tingkat : null);
        $rows = Siswa::search($q !== '' ? $q : null, $tingkat !== '' ? $tingkat : null, $perPage, ($page - 1) * $perPage);

        view('layout/main', [
            'title' => 'Data Siswa',
            'content' => 'siswa/index',
            'rows' => $rows,
            'q' => $q,
            'tingkat' => $tingkat,
            'tingkatList' => Siswa::tingkatList(),
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function createForm(): void
    {
        require_login();
        view('layout/main', [
            'title' => 'Tambah Siswa',
            'content' => 'siswa/form',
            'siswa' => null,
            'action' => url('/siswa/store'),
            'error' => flash('error'),
        ]);
    }

    public function store(): void
    {
        require_login();
        verify_csrf();
        try {
            $data = $this->fromRequest($_POST);
            if ($data['nis_lokal'] === '' || $data['nama'] === '') {
                throw new \InvalidArgumentException('NIS Lokal dan Nama wajib diisi.');
            }
            if (Siswa::findByNis($data['nis_lokal'])) {
                throw new \InvalidArgumentException('NIS Lokal sudah terpakai.');
            }
            Siswa::create($data);
            flash('success', 'Siswa berhasil ditambahkan.');
            redirect('/siswa');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/siswa/create');
        }
    }

    public function editForm(int $id): void
    {
        require_login();
        $siswa = Siswa::find($id);
        if (!$siswa) {
            flash('error', 'Data siswa tidak ditemukan.');
            redirect('/siswa');
        }
        view('layout/main', [
            'title' => 'Edit Siswa',
            'content' => 'siswa/form',
            'siswa' => $siswa,
            'action' => url('/siswa/update/' . $id),
            'error' => flash('error'),
        ]);
    }

    public function update(int $id): void
    {
        require_login();
        verify_csrf();
        $siswa = Siswa::find($id);
        if (!$siswa) {
            flash('error', 'Data siswa tidak ditemukan.');
            redirect('/siswa');
        }
        try {
            $data = $this->fromRequest($_POST);
            if ($data['nis_lokal'] === '' || $data['nama'] === '') {
                throw new \InvalidArgumentException('NIS Lokal dan Nama wajib diisi.');
            }
            $other = Siswa::findByNis($data['nis_lokal']);
            if ($other && (int) $other['id'] !== $id) {
                throw new \InvalidArgumentException('NIS Lokal sudah dipakai siswa lain.');
            }
            Siswa::update($id, $data);
            flash('success', 'Data siswa diperbarui.');
            redirect('/siswa');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/siswa/edit/' . $id);
        }
    }

    public function destroy(int $id): void
    {
        require_login();
        verify_csrf();
        Siswa::delete($id);
        flash('success', 'Data siswa dihapus.');
        redirect('/siswa');
    }

    private function fromRequest(array $post): array
    {
        $data = [];
        foreach (Siswa::fields() as $f) {
            $raw = isset($post[$f]) ? $post[$f] : null;
            if (is_string($raw)) {
                $raw = trim($raw);
            }
            if (in_array($f, ['tanggal_lahir', 'tanggal_terima', 'mutasi_tanggal', 'keluar_tanggal'], true)) {
                $data[$f] = ($raw === null || $raw === '') ? null : parse_tanggal_flexible($raw);
            } elseif ($f === 'jenis_kelamin') {
                $data[$f] = normalize_jk((string) $raw);
            } elseif ($f === 'anak_ke') {
                $data[$f] = ($raw === null || $raw === '') ? null : (int) $raw;
            } else {
                $data[$f] = ($raw === '') ? null : $raw;
            }
        }
        return $data;
    }
}
