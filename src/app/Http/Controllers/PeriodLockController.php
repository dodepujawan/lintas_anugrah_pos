<?php

namespace App\Http\Controllers;

use App\Models\YearClosing;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PeriodLockController extends Controller
{
    public function index()
    {
        $currentYear = now()->year;
        $years = collect(range($currentYear - 5, $currentYear + 1))
            ->merge(YearClosing::pluck('year')->map(fn($year) => (int) $year))
            ->unique()
            ->sortDesc()
            ->values();

        $locks = YearClosing::whereIn('year', $years->map(fn($year) => (string) $year))
            ->get()
            ->keyBy('year');

        return view('period-locks.index', compact('years', 'locks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $lock = YearClosing::updateOrCreate(
            ['year' => (string) $validated['year']],
            [
                'closed_at' => now(),
                'notes' => $validated['notes'] ?? 'Dikunci manual oleh admin.',
            ]
        );

        app(AuditLogService::class)->record(
            'lock',
            'period-lock',
            $lock,
            "Mengunci periode tahun {$validated['year']}.",
            ['year' => (string) $validated['year']]
        );

        return redirect()->route('period-locks.index')
            ->with('success', "Periode tahun {$validated['year']} berhasil dikunci.");
    }

    public function destroy(string $year)
    {
        if (! preg_match('/^[0-9]{4}$/', $year)) {
            return redirect()->route('period-locks.index')
                ->with('error', 'Tahun periode tidak valid.');
        }

        $lock = YearClosing::where('year', $year)->first();

        if (! $lock) {
            return redirect()->route('period-locks.index')
                ->with('warning', "Periode tahun {$year} belum memiliki status kunci.");
        }

        $lock->update([
            'closed_at' => null,
            'notes' => trim((string) $lock->notes . "\nDibuka manual oleh admin pada " . Carbon::now()->format('Y-m-d H:i:s')),
        ]);

        app(AuditLogService::class)->record(
            'unlock',
            'period-lock',
            $lock,
            "Membuka kunci periode tahun {$year}.",
            ['year' => $year]
        );

        return redirect()->route('period-locks.index')
            ->with('success', "Kunci periode tahun {$year} berhasil dibuka.");
    }
}
