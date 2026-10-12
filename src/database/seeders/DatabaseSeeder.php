<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */

    // php artisan db:seed --class=DatabaseSeed
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RoleMenuSeeder::class,
            CoaSeeder::class,
            McustomerSeeder::class,
            MobilSeeder::class,
        ]);
    }
}
