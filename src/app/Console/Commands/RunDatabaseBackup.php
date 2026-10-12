<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class RunDatabaseBackup extends Command
{
    protected $signature = 'app:backup-database';

    protected $description = 'Create or replace the rolling SQL database backup.';

    public function handle(DatabaseBackupService $backupService): int
    {
        $backup = $backupService->create();
        $this->info('Backup berhasil dibuat: ' . $backup['name']);

        return self::SUCCESS;
    }
}
