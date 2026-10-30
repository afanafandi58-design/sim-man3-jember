<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LmsGuruScopeService
{
    public function currentUser(): User
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        return $user;
    }

    public function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function isKepala(User $user): bool
    {
        return $user->role === 'kepala';
    }

    public function isGuru(User $user): bool
    {
        return $user->role === 'guru' && $user->guru_id;
    }

    public function assertGuru(User $user): void
    {
        if (! $this->isGuru($user) && ! $this->isAdmin($user)) {
            abort(403, 'Hanya guru atau admin yang dapat mengelola konten LMS.');
        }
    }

    /** @param  Model&object{guru_id?: int|null}  $model */
    public function assertOwnsOrStaff(Model $model, User $user): void
    {
        if ($this->isAdmin($user) || $this->isKepala($user)) {
            return;
        }

        if (! $this->isGuru($user)) {
            abort(403);
        }

        if (! isset($model->guru_id) || (int) $model->guru_id !== (int) $user->guru_id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }
    }

    public function guruIdForQuery(User $user): ?int
    {
        if ($this->isGuru($user)) {
            return (int) $user->guru_id;
        }

        return null;
    }

    public function resolveGuruIdForStore(User $user, ?int $jadwalId): int
    {
        if ($this->isGuru($user)) {
            return (int) $user->guru_id;
        }

        if ($this->isAdmin($user) && $jadwalId) {
            $guruId = Jadwal::query()->whereKey($jadwalId)->value('guru_id');
            if ($guruId) {
                return (int) $guruId;
            }
        }

        abort(422, 'Pilih jadwal yang valid untuk menentukan guru pengampu.');
    }
}
