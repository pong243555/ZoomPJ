<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! is_string($email) || $email === '' || ! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Set ADMIN_EMAIL and an ADMIN_PASSWORD of at least 12 characters before creating the administrator.');
        }

        $existingUser = DB::table('users')->where('email', $email)->first();
        $now = now();

        DB::table('users')->updateOrInsert(
            ['email' => $email],
            [
                'user_id' => $existingUser?->user_id ?? ((int) DB::table('users')->max('user_id') + 1),
                'name' => env('ADMIN_NAME', 'Administrator'),
                'surname' => env('ADMIN_SURNAME', 'Account'),
                'username' => env('ADMIN_USERNAME', 'admin'),
                'password' => Hash::make($password),
                'role' => 'admin',
                'updated_at' => $now,
                'created_at' => $existingUser?->created_at ?? $now,
            ]
        );
    }
}
