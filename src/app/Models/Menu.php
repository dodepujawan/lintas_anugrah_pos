<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Menu extends Model
{
    use HasFactory;

    /** @var array<string, self|null> */
    protected static array $resolvedRouteCache = [];

    protected $fillable = [
        'name',
        'route_name',
        'section',
        'icon',
        'sort_order',
        'is_sidebar',
        'description',
    ];

    protected $casts = [
        'is_sidebar' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_menu')
            ->withPivot([
                'can_view',
                'can_create',
                'can_edit',
                'can_delete',
                'can_post',
                'can_void',
                'can_export',
            ])
            ->withTimestamps();
    }

    public function scopeSidebar($query)
    {
        return $query->where('is_sidebar', true)
            ->orderByRaw("CASE
                WHEN route_name IN ('penjualan-hosting', 'import-data') THEN 5
                WHEN section = 'Menu Utama' THEN 1
                WHEN section = 'Saldo Awal' THEN 2
                WHEN section = 'Transaksi' THEN 3
                WHEN section = 'Master' THEN 4
                WHEN section = 'Laporan' THEN 5
                WHEN section IN ('Setting', 'Pengaturan') THEN 6
                ELSE 999
            END")
            ->orderBy('sort_order');
    }

    public static function resolveByRoute(string $routeName): ?self
    {
        if (array_key_exists($routeName, static::$resolvedRouteCache)) {
            return static::$resolvedRouteCache[$routeName];
        }

        $route = $routeName;

        while ($route !== '') {
            $menu = static::where('route_name', $route)->first();

            if ($menu) {
                return static::$resolvedRouteCache[$routeName] = $menu;
            }

            $position = strrpos($route, '.');

            if ($position === false) {
                break;
            }

            $route = substr($route, 0, $position);
        }

        return static::$resolvedRouteCache[$routeName] = null;
    }

    public static function permissionForRoute(string $routeName, string $method): string
    {
        if (str_ends_with($routeName, '.destroy') || $method === 'DELETE') {
            return 'delete';
        }

        if (in_array($routeName, ['customers.duplicates', 'customers.duplicates.export', 'customers.merge'], true)) {
            return 'delete';
        }

        if (
            str_contains($routeName, 'tutup_buku.remove_closing_journal')
            || str_contains($routeName, 'tutup_buku.reset_year_close')
        ) {
            return 'delete';
        }

        if (
            str_contains($routeName, 'tutup_buku.generate_jurnal')
            || str_contains($routeName, 'tutup_buku.mark_closed')
            || str_contains($routeName, 'tutup_buku.archive')
            || str_contains($routeName, 'tutup_buku.close_year')
        ) {
            return 'post';
        }

        if (str_ends_with($routeName, '.post')) {
            return 'post';
        }

        if (str_ends_with($routeName, '.void')) {
            return 'void';
        }

        if (str_contains($routeName, '.export') || str_contains($routeName, '.excel')) {
            return 'export';
        }

        if (str_ends_with($routeName, '.create') || str_ends_with($routeName, '.store') || str_ends_with($routeName, '.draft.save')) {
            return 'create';
        }

        if (str_ends_with($routeName, '.edit') || str_ends_with($routeName, '.update')) {
            return 'edit';
        }

        if (str_ends_with($routeName, '.fix')) {
            return 'edit';
        }

        return 'view';
    }

    public static function sidebarForUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        $user->loadMissing('role');

        if (! $user->role) {
            return collect();
        }

        $isAdministrator = $user->role->name === 'Administrator';

        return static::sidebar()
            ->where(function ($query) use ($user, $isAdministrator) {
                $query->whereHas('roles', function ($roleQuery) use ($user) {
                    $roleQuery->where('roles.id', $user->role_id)
                        ->where('role_menu.can_view', true);
                });

                if ($isAdministrator) {
                    $query->orWhere('route_name', 'settings');
                }
            })
            ->get();
    }
}
