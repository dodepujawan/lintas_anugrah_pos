<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstWhere('name', 'Administrator');

        User::updateOrCreate(
            ['email' => 'admin@akuntansi.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@akuntansi.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role_id' => $adminRole?->id,
            ]
        );

        echo "✅ Admin user berhasil dibuat!\n";
    }
}
