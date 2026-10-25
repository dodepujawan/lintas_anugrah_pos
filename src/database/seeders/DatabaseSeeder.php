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
            RoleMenuSeeder::class,
            InvoiceExpedisiAccessSeeder::class,
            UserSeeder::class,
            InvoiceExpedisiAccessSeeder::class,
            CoaSeeder::class,
            McustomerSeeder::class,
            MobilSeeder::class,
            TransaksiPermissionSeeder::class,
        ]);
    }
}
