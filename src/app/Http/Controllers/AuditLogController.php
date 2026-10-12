<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AuditLogExport;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('created_at', '>=', $request->tanggal_awal);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->tanggal_akhir);
        }

        $logs = $query->paginate(25)->withQueryString();
        $modules = AuditLog::select('module')->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $users = User::orderBy('name')->get(['id', 'name', 'username']);

        return view('audit-logs.index', compact('logs', 'modules', 'actions', 'users'));
    }

    public function export(Request $request)
    {
        return Excel::download(
            new AuditLogExport($request->only(['module', 'action', 'user_id', 'tanggal_awal', 'tanggal_akhir'])),
            'audit_trail_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}
