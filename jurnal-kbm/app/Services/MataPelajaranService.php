<?php

namespace App\Services;

use App\Models\MataPelajaran;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MataPelajaranService
{
    public function activeList(): Collection
    {
        return MataPelajaran::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();
    }

    public function paginate(): LengthAwarePaginator
    {
        return MataPelajaran::query()
            ->orderBy('nama')
            ->paginate(20);
    }

    public function store(array $data): MataPelajaran
    {
        $this->assertUniqueNama($data['nama']);

        return MataPelajaran::create([
            'nama' => trim($data['nama']),
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(MataPelajaran $mataPelajaran, array $data): MataPelajaran
    {
        if (trim($data['nama']) !== $mataPelajaran->nama) {
            $this->assertUniqueNama($data['nama'], $mataPelajaran->id);
        }

        $mataPelajaran->update([
            'nama' => trim($data['nama']),
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $mataPelajaran->fresh();
    }

    public function delete(MataPelajaran $mataPelajaran): void
    {
        $mataPelajaran->update(['is_active' => false]);
    }

    private function assertUniqueNama(string $nama, ?int $excludeId = null): void
    {
        $exists = MataPelajaran::query()
            ->where('nama', trim($nama))
            ->when($excludeId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'nama' => 'Nama mata pelajaran sudah ada di master.',
            ]);
        }
    }
}
