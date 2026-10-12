<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class BackupController extends Controller
{
    public function index(DatabaseBackupService $backupService): View
    {
        return view('backup.index', [
            'files' => $backupService->files(),
        ]);
    }

    public function run(Request $request, DatabaseBackupService $backupService)
    {
        try {
            $backup = $backupService->create();
            return redirect()->route('backup')->with('success', "Backup berhasil dibuat: {$backup['name']}");
        } catch (\Exception $e) {
            return redirect()->route('backup')->with('error', "Backup gagal: " . $e->getMessage());
        }
    }

    public function download(string $file)
    {
        $file = basename($file);
        $path = storage_path('app/backups' . DIRECTORY_SEPARATOR . $file);

        if (! str_ends_with($file, '.sql') || ! File::exists($path)) {
            abort(404);
        }

        return Response::download($path, $file);
    }
}
