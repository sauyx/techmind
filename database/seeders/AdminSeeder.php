<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (empty($email) || empty($password)) {
            if (app()->environment('production')) {
                throw new \RuntimeException('ADMIN_EMAIL dan ADMIN_PASSWORD wajib diisi di production.');
            }

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin TechMind',
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
