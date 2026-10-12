<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class SyncUserRoles extends Command
{
    protected $signature = 'users:sync-roles';

    protected $description = 'Sync users role_old to Spatie roles';

    public function handle()
    {
        $users = User::whereNotNull('role_old')
            ->where('role_old', '!=', '')
            ->get();

        $roles = Role::all()
            ->keyBy(fn ($role) => strtolower(trim($role->name)));

        $success = 0;
        $skipped = 0;

        $this->info("Memproses {$users->count()} user...");
        $this->newLine();

        foreach ($users as $user) {

            $roleName = trim($user->role_old);
            $roleKey = strtolower($roleName);

            if (!$roles->has($roleKey)) {

                $this->warn(
                    "SKIP  {$user->name} → {$roleName} (role tidak ditemukan)"
                );

                $skipped++;
                continue;
            }

            $spatieRole = $roles->get($roleKey);

            $user->syncRoles($spatieRole);

            $this->line(
                "OK    {$user->name} → {$spatieRole->name}"
            );

            $success++;
        }

        $this->newLine();
        $this->info('========================');
        $this->info("BERHASIL : {$success}");
        $this->warn("SKIP     : {$skipped}");
        $this->info("TOTAL    : {$users->count()}");
        $this->info('========================');

        return self::SUCCESS;
    }
}
