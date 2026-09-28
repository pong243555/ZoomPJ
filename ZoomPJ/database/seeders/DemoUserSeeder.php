<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('The demo user can only be created in a local or testing environment.');
        }

        $email = env('DEMO_USER_EMAIL');
        $password = env('DEMO_USER_PASSWORD');

        if (! is_string($email) || $email === '' || ! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Set DEMO_USER_EMAIL and a DEMO_USER_PASSWORD of at least 12 characters in .env.');
        }

        if (DB::table('users')->where('email', $email)->exists()) {
            $this->command?->warn('A user with DEMO_USER_EMAIL already exists; no account or password was changed.');

            return;
        }

        DB::table('users')->insert([
            'user_id' => ((int) DB::table('users')->max('user_id')) + 1,
            'name' => env('DEMO_USER_NAME', 'Demo'),
            'surname' => env('DEMO_USER_SURNAME', 'User'),
            'email' => $email,
            'username' => env('DEMO_USER_USERNAME', 'demo-user'),
            'password' => Hash::make($password),
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command?->info("Created demo user {$email}.");
    }
}
