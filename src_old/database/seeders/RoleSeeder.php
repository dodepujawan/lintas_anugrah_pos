<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'staff']);
        Role::firstOrCreate(['name' => 'driver']);
        Role::firstOrCreate(['name' => 'customer']);

        $users = User::all();

        foreach ($users as $user) {
            if ($user->role_old) {
                $user->syncRoles([$user->role_old]);
            }
        }
    }
}
