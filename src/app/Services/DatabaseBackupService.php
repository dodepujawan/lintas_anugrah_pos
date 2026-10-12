<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackupService
{
    public function create(): array
    {
        set_time_limit(0);

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        $dbName = $config['database'] ?? '';

        if (! $dbName) {
            throw new \RuntimeException('Konfigurasi database belum lengkap.');
        }

        $backupPath = storage_path('app/backups');
        if (! File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $fileName = "{$dbName}_backup.sql";
        $filePath = $backupPath . DIRECTORY_SEPARATOR . $fileName;
        $temporaryPath = $filePath . '.tmp-' . bin2hex(random_bytes(6));
        $handle = fopen($temporaryPath, 'wb');

        if ($handle === false) {
            throw new \RuntimeException('File backup tidak dapat dibuat.');
        }

        try {
            $this->write($handle, "/* Backup Database {$dbName} - " . now() . " */\n");
            $this->write($handle, "/* Collation dibuat kompatibel untuk MySQL/MariaDB lokal. */\n");
            $this->write($handle, "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;\n");
            $this->write($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            foreach ($this->tables() as $tableName) {
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (! empty($createTable)) {
                    $createTableArray = (array) $createTable[0];
                    if (isset($createTableArray['Create Table'])) {
                        $definition = $this->makeCollationCompatible($createTableArray['Create Table']);
                        $this->write($handle, $definition . ";\n\n");
                    }
                }

                DB::table($tableName)->orderBy(DB::raw('1'))->chunk(500, function ($rows) use ($handle, $tableName) {
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $columns = array_keys($rowArray);
                        $values = array_map(
                            fn($value) => is_null($value) ? 'NULL' : DB::getPdo()->quote($value),
                            array_values($rowArray)
                        );

                        $this->write(
                            $handle,
                            "INSERT INTO `{$tableName}` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(', ', $values) . ");\n"
                        );
                    }
                });

                $this->write($handle, "\n");
            }

            $this->write($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } catch (\Throwable $e) {
            fclose($handle);
            File::delete($temporaryPath);
            throw $e;
        }

        fclose($handle);

        $previousPath = null;
        if (File::exists($filePath)) {
            $previousPath = $filePath . '.previous-' . bin2hex(random_bytes(6));
            if (! rename($filePath, $previousPath)) {
                File::delete($temporaryPath);
                throw new \RuntimeException('Backup lama tidak dapat diamankan untuk diganti.');
            }
        }

        if (! rename($temporaryPath, $filePath)) {
            if ($previousPath !== null && ! rename($previousPath, $filePath)) {
                throw new \RuntimeException('Backup baru gagal dipasang dan backup sebelumnya gagal dipulihkan.');
            }

            File::delete($temporaryPath);
            throw new \RuntimeException('Backup baru gagal menggantikan file backup sebelumnya.');
        }

        if ($previousPath !== null) {
            File::delete($previousPath);
        }

        $legacyBackupPattern = '/^' . preg_quote($dbName, '/') . '_backup_\d{8}_\d{6}\.sql$/';
        foreach (File::files($backupPath) as $existingFile) {
            if (preg_match($legacyBackupPattern, $existingFile->getFilename()) === 1
                && ! File::delete($existingFile->getPathname())) {
                throw new \RuntimeException('Backup terbaru berhasil dibuat, tetapi backup lama tidak dapat dibersihkan.');
            }
        }

        return [
            'name' => $fileName,
            'path' => $filePath,
            'size' => File::size($filePath),
        ];
    }

    public function files(): array
    {
        $backupPath = storage_path('app/backups');
        if (! File::exists($backupPath)) {
            return [];
        }

        return collect(File::files($backupPath))
            ->filter(fn($file) => $file->getExtension() === 'sql')
            ->sortByDesc(fn($file) => $file->getMTime())
            ->map(fn($file) => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified_at' => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
            ])
            ->values()
            ->all();
    }

    private function tables(): array
    {
        return collect(DB::select('SHOW TABLES'))
            ->map(fn($row) => array_values((array) $row)[0] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Ubah collation dari server baru menjadi collation yang didukung luas.
     */
    private function makeCollationCompatible(string $sql): string
    {
        return preg_replace(
            '/utf8mb4_(?:uca1400|0900)_[a-z0-9_]+/i',
            'utf8mb4_unicode_ci',
            $sql
        ) ?? $sql;
    }

    /** @param resource $handle */
    private function write($handle, string $content): void
    {
        if (fwrite($handle, $content) === false) {
            throw new \RuntimeException('Gagal menulis isi file backup.');
        }
    }
}
