<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'dwebpro@gmail.com'], // biar gak dobel
            [
                'name'     => 'dwebpro',
                'user_id'  => 'AD0001',
                'password' => Hash::make('admin123'),
                'role_old'    => 'admin',
            ]
        );
        User::updateOrCreate(
            ['email' => 'kantorutama@gmail.com'], // unique key
            [
                'name'     => 'Kantor Utama',
                'user_id'  => 'AD0002',
                'password' => Hash::make('admin123'),
                'role_old'    => 'admin',
            ]
        );
    }
}
