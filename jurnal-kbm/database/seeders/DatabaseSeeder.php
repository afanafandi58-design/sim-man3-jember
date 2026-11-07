<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('ADMIN_DEFAULT_PASSWORD');

        if (! $password) {
            if (app()->environment('production')) {
                throw new \RuntimeException('ADMIN_DEFAULT_PASSWORD wajib diisi di environment produksi.');
            }

            $password = 'Man3Jember!Admin2026';
        }

        $user = User::query()->firstOrNew(['email' => 'admin@man3jember.sch.id']);
        $user->fill([
            'name' => 'Administrator SIM MAN 3 Jember',
            'password' => Hash::make($password),
        ]);
        $user->forceFill([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $user->save();
    }
}
