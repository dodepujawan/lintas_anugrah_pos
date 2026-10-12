<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();

        if (! Auth::check() || ! $route) {
            return $next($request);
        }

        $routeName = $route->getName();

        if (! $routeName) {
            return $next($request);
        }

        $menu = Menu::resolveByRoute($routeName);

        if (! $menu) {
            return $next($request);
        }

        $permission = Menu::permissionForRoute($routeName, $request->method());

        // Penyimpanan draft dipakai oleh form tambah dan form edit.
        if (str_ends_with($routeName, '.draft.save')) {
            $canSaveDraft = Auth::user()->hasPermissionForMenu($menu, 'create')
                || Auth::user()->hasPermissionForMenu($menu, 'edit');

            if ($canSaveDraft) {
                return $next($request);
            }
        }

        $allowed = in_array($routeName, ['customers.duplicates', 'customers.duplicates.export', 'customers.merge'], true)
            ? Auth::user()->hasExplicitMenuPermission('customers', 'delete')
            : Auth::user()->hasPermissionForMenu($menu, $permission);

        if (! $allowed) {
            $fallbackRoute = Auth::user()->defaultAuthorizedRoute();

            if ($fallbackRoute === $routeName) {
                abort(403, 'Anda tidak memiliki hak akses untuk menu ini.');
            }

            return redirect()->route($fallbackRoute)
                ->with('error', 'Anda tidak memiliki hak akses untuk menu ini.');
        }

        return $next($request);
    }
}
