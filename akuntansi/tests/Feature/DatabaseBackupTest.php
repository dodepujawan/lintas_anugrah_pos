<?php

namespace Tests\Feature;

use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    public function test_create_replaces_current_backup_and_cleans_legacy_files(): void
    {
        $databaseName = 'backup_rotation_'.bin2hex(random_bytes(6));
        $backupDirectory = storage_path('app/backups');

        if (! File::exists($backupDirectory)) {
            File::makeDirectory($backupDirectory, 0755, true);
        }

        $legacyPath = $backupDirectory.DIRECTORY_SEPARATOR.$databaseName.'_backup_20261001_230000.sql';
        File::put($legacyPath, 'old backup');

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => $databaseName,
        ]);
        DB::shouldReceive('select')
            ->with('SHOW TABLES')
            ->twice()
            ->andReturn([]);

        $backupService = app(DatabaseBackupService::class);
        $firstBackup = $backupService->create();
        $secondBackup = $backupService->create();

        $this->assertSame($databaseName.'_backup.sql', $secondBackup['name']);
        $this->assertSame($firstBackup['path'], $secondBackup['path']);
        $this->assertFileExists($secondBackup['path']);
        $this->assertFileDoesNotExist($legacyPath);
        $this->assertSame(1, count(array_filter(
            $backupService->files(),
            fn (array $file) => str_starts_with($file['name'], $databaseName.'_backup')
        )));

        File::delete($secondBackup['path']);
    }
}
