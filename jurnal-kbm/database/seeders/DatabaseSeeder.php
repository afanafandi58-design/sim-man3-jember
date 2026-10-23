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

        User::updateOrCreate(
            ['email' => 'admin@man3jember.sch.id'],
            [
                'name' => 'Administrator SIM MAN 3 Jember',
                'role' => 'admin',
                'password' => Hash::make($password),
            ]
        );
    }
}
