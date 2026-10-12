<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateFresh extends Command
{
    protected $signature = 'app:fresh-db';

    protected $description = 'Reset database dan auto-seed dengan data awal. Diblokir di production.';

    public function handle(): int
    {
        if (app()->environment(['production', 'online']) && ! config('safety.allow_destructive_commands')) {
            $this->error('Command ini diblokir di production karena akan menghapus seluruh tabel database.');
            $this->warn('Backup database dulu dan set ALLOW_DESTRUCTIVE_COMMANDS=true sementara hanya jika benar-benar diperlukan.');

            return self::FAILURE;
        }

        if (! $this->confirm('PERINGATAN: command ini akan menghapus seluruh tabel dan isi database. Lanjutkan?', false)) {
            $this->warn('Dibatalkan. Database tidak diubah.');

            return self::FAILURE;
        }

        $this->info('Dropping all tables...');

        $exitCode = $this->call('migrate:fresh', [
            '--seed' => true,
        ]);

        if ($exitCode !== self::SUCCESS) {
            return $exitCode;
        }

        $this->info('');
        $this->info('Database siap digunakan.');
        $this->info('');
        $this->table(
            ['Keterangan', 'Value'],
            [
                ['Admin Email', 'admin@akuntansi.com'],
                ['Admin Password', 'password'],
                ['Tables Created', '8+ tables'],
                ['Seeders Run', 'User, CoA, Customer'],
            ]
        );

        return self::SUCCESS;
    }
}
