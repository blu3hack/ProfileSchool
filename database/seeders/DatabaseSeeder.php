<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Akun administrator panel. Ganti kata sandinya setelah login pertama.
        User::updateOrCreate(
            ['email' => 'admin@alazka.sch.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->call(SiteContentSeeder::class);
        $this->call(ShortlinkSeeder::class);
    }
}
