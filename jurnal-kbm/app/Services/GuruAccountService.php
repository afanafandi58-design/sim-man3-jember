<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class GuruAccountService
{
    public const EMAIL_DOMAIN = '@man3jember.sch.id';

    public const MAX_LOCAL_LENGTH = 30;

    public const SESSION_CREDENTIALS_KEY = 'guru_account_credentials';

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->applyFilters(Guru::query()->with('user'), $filters)
            ->orderBy('kode_guru')
            ->paginate(10)
            ->withQueryString();
    }

    public function mapelOptions(): array
    {
        return Guru::query()
            ->distinct()
            ->orderBy('mapel')
            ->pluck('mapel')
            ->filter()
            ->values()
            ->all();
    }

    public function createAccount(Guru $guru): array
    {
        $guru->loadMissing('user');

        if ($guru->user) {
            throw ValidationException::withMessages([
                'account' => 'Guru ini sudah memiliki akun login.',
            ]);
        }

        $password = $this->generateRandomPassword();
        $email = $this->generateUsername($guru);

        $user = User::create([
            'name' => $guru->displayName(),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'guru',
            'guru_id' => $guru->id,
            'is_active' => true,
            'password_reset_at' => now(),
        ]);

        $this->rememberCredential($guru->id, $email, $password);

        return [
            'user' => $user,
            'email' => $email,
            'password' => $password,
        ];
    }

    public function provisionFromImport(Guru $guru): User
    {
        $user = User::firstOrNew(['guru_id' => $guru->id]);
        $email = $this->generateUsername($guru, $user->id);

        $user->fill([
            'name' => $guru->displayName(),
            'email' => $email,
            'role' => 'guru',
            'is_active' => true,
        ]);

        if (! $user->exists) {
            $password = $this->generateRandomPassword();
            $user->password = Hash::make($password);
            $user->password_reset_at = now();
            $this->rememberCredential($guru->id, $email, $password);
        }

        $user->save();

        return $user;
    }

    public function syncAccountProfile(Guru $guru): ?User
    {
        $guru->loadMissing('user');

        if (! $guru->user) {
            return null;
        }

        $guru->user->update([
            'name' => $guru->displayName(),
            'email' => $this->generateUsername($guru, $guru->user->id),
        ]);

        return $guru->user->refresh();
    }

    public function resetPassword(User $user): string
    {
        $this->assertGuruAccount($user);

        $password = $this->generateRandomPassword();
        $user->update([
            'password' => Hash::make($password),
            'password_reset_at' => now(),
        ]);

        if ($user->guru_id) {
            $this->rememberCredential($user->guru_id, $user->email, $password);
        }

        return $password;
    }

    public function deactivate(User $user): void
    {
        $this->assertGuruAccount($user);

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'account' => 'Akun guru sudah dalam status nonaktif.',
            ]);
        }

        $user->update(['is_active' => false]);
    }

    public function activate(User $user): void
    {
        $this->assertGuruAccount($user);

        if ($user->is_active) {
            throw ValidationException::withMessages([
                'account' => 'Akun guru sudah dalam status aktif.',
            ]);
        }

        $user->update(['is_active' => true]);
    }

    public function generateUsername(Guru $guru, ?int $ignoreUserId = null): string
    {
        $localPart = $this->buildLocalPart($guru->displayName());

        if ($guru->username_suffix) {
            $suffix = '.'.$guru->username_suffix;
            $base = strlen($localPart) + strlen($suffix) > self::MAX_LOCAL_LENGTH
                ? substr($localPart, 0, self::MAX_LOCAL_LENGTH - strlen($suffix))
                : $localPart;
            $localPart = rtrim($base, '.').$suffix;
        }

        $email = $localPart.self::EMAIL_DOMAIN;

        if ($this->emailExists($email, $ignoreUserId)) {
            return $this->generateUsernameWithLetterSuffix($localPart, $ignoreUserId);
        }

        return $email;
    }

    public function accountStatusLabel(Guru $guru): string
    {
        if (! $guru->user) {
            return 'Belum Memiliki Akun';
        }

        return $guru->user->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function rememberCredential(int $guruId, string $email, string $password): void
    {
        $credentials = session(self::SESSION_CREDENTIALS_KEY, []);
        $credentials[$guruId] = [
            'email' => $email,
            'password' => $password,
            'stored_at' => now()->toDateTimeString(),
        ];

        session([self::SESSION_CREDENTIALS_KEY => $credentials]);
    }

    public function getSessionCredentials(): array
    {
        return session(self::SESSION_CREDENTIALS_KEY, []);
    }

    public function getPrintableAccounts(?array $guruIds = null): Collection
    {
        $query = Guru::query()->with('user')->orderBy('kode_guru');

        if (! empty($guruIds)) {
            $query->whereIn('id', $guruIds);
        }

        $credentials = $this->getSessionCredentials();

        return $query->get()->map(function (Guru $guru) use ($credentials) {
            $credential = $credentials[$guru->id] ?? null;

            return [
                'kode_guru' => $guru->kode_guru,
                'nama' => $guru->displayName(),
                'username' => $guru->user?->email,
                'password' => $credential['password'] ?? '-',
                'status' => $this->accountStatusLabel($guru),
            ];
        });
    }

    public function resolveDuplicateIdentity(string $nama): array
    {
        $baseNama = $this->stripGelar($nama);
        $nameKey = $this->normalizeNameKey($baseNama);

        $matches = Guru::query()
            ->get()
            ->filter(fn (Guru $guru) => $this->normalizeNameKey($this->stripGelar($guru->nama)) === $nameKey);

        if ($matches->isEmpty()) {
            return [
                'nama_tampilan' => null,
                'username_suffix' => null,
            ];
        }

        $suffixIndex = $matches->count();
        $suffixLetter = $this->suffixLetterForIndex($suffixIndex);

        return [
            'nama_tampilan' => trim($baseNama).' ('.strtoupper($suffixLetter).')',
            'username_suffix' => $suffixLetter,
        ];
    }

    private function applyFilters($query, array $filters)
    {
        $nama = trim((string) ($filters['nama'] ?? ''));
        $nip = trim((string) ($filters['nip'] ?? ''));
        $mapel = trim((string) ($filters['mapel'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return $query
            ->when($nama !== '', fn ($builder) => $builder->where(function ($inner) use ($nama) {
                $inner->where('nama', 'like', "%{$nama}%")
                    ->orWhere('nama_tampilan', 'like', "%{$nama}%");
            }))
            ->when($nip !== '', fn ($builder) => $builder->where('nip', 'like', "%{$nip}%"))
            ->when($mapel !== '', fn ($builder) => $builder->where('mapel', $mapel))
            ->when($status === 'belum', fn ($builder) => $builder->whereDoesntHave('user'))
            ->when($status === 'aktif', fn ($builder) => $builder->whereHas('user', fn ($user) => $user->where('is_active', true)))
            ->when($status === 'nonaktif', fn ($builder) => $builder->whereHas('user', fn ($user) => $user->where('is_active', false)));
    }

    private function generateUsernameWithLetterSuffix(string $localPart, ?int $ignoreUserId = null): string
    {
        foreach (range('a', 'z') as $letter) {
            $suffix = '.'.$letter;
            $base = strlen($localPart) + strlen($suffix) > self::MAX_LOCAL_LENGTH
                ? substr($localPart, 0, self::MAX_LOCAL_LENGTH - strlen($suffix))
                : $localPart;
            $candidate = rtrim($base, '.').$suffix.self::EMAIL_DOMAIN;

            if (! $this->emailExists($candidate, $ignoreUserId)) {
                return $candidate;
            }
        }

        throw ValidationException::withMessages([
            'email' => 'Tidak dapat membuat username unik untuk guru ini.',
        ]);
    }

    private function buildLocalPart(string $nama): string
    {
        $nama = $this->stripGelar($nama);
        $nama = strtolower($nama);
        $nama = preg_replace('/[^a-z0-9\s]/', '', $nama) ?? '';
        $nama = preg_replace('/\s+/', '.', trim($nama)) ?? '';
        $nama = preg_replace('/\.+/', '.', $nama) ?? '';
        $nama = trim($nama, '.');

        if ($nama === '') {
            return 'guru';
        }

        if (strlen($nama) > self::MAX_LOCAL_LENGTH) {
            $nama = substr($nama, 0, self::MAX_LOCAL_LENGTH);
            $nama = rtrim($nama, '.');
            $lastDot = strrpos($nama, '.');

            if ($lastDot !== false && $lastDot > 0) {
                $nama = substr($nama, 0, $lastDot);
            }
        }

        return $nama !== '' ? $nama : 'guru';
    }

    private function stripGelar(string $nama): string
    {
        $nama = trim(explode(',', $nama)[0]);
        $gelarPattern = '/\s+(S\.?\s*Pd\.?I?|M\.?\s*Pd\.?I?|S\.?\s*Kom\.?|S\.?\s*Si\.?|S\.?\s*T\.?|S\.?\s*H\.?|S\.?\s*E\.?|S\.?\s*Ag\.?|S\.?\s*Sn\.?|A\.?\s*Md\.?|Dr\.?|Prof\.?)$/iu';

        return trim(preg_replace($gelarPattern, '', $nama) ?? $nama);
    }

    private function normalizeNameKey(string $nama): string
    {
        $nama = strtolower($nama);
        $nama = preg_replace('/[^a-z0-9]/', '', $nama) ?? '';

        return $nama;
    }

    private function suffixLetterForIndex(int $index): string
    {
        return chr(96 + min($index, 26));
    }

    private function emailExists(string $email, ?int $ignoreUserId = null): bool
    {
        return User::query()
            ->where('email', $email)
            ->when($ignoreUserId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->exists();
    }

    private function generateRandomPassword(): string
    {
        $length = random_int(8, 10);
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    private function assertGuruAccount(User $user): void
    {
        if ($user->role !== 'guru' || ! $user->guru_id) {
            throw ValidationException::withMessages([
                'account' => 'Akun ini bukan akun guru yang valid.',
            ]);
        }
    }
}
