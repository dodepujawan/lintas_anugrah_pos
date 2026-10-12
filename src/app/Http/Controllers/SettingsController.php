<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $users = User::with('role')->orderBy('name')->get();
        $roles = Role::with('menus')->orderBy('name')->get();
        $menus = Menu::orderBy('section')->orderBy('sort_order')->get();
        $routeNames = collect(Route::getRoutes())->map(fn($route) => $route->getName())->filter()->unique()->sort()->values();

        return view('settings.index', compact('users', 'roles', 'menus', 'routeNames'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:' . User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->input('role_id'),
        ]);

        return redirect()->route('settings.index')->with('status', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:' . User::class . ',username,' . $user->id],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class . ',email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role_id' => $request->input('role_id'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('settings.index')->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('settings.index')->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('settings.index')->with('status', 'User berhasil dihapus.');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Role::create($request->only(['name', 'description']));

        return redirect()->route('settings.index')->with('status', 'Role berhasil ditambahkan.');
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role->update($request->only(['name', 'description']));

        return redirect()->route('settings.index')->with('status', 'Role berhasil diperbarui.');
    }

    public function destroyRole(Role $role): RedirectResponse
    {
        $role->delete();

        return redirect()->route('settings.index')->with('status', 'Role berhasil dihapus.');
    }

    public function storeMenu(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'route_name' => ['required', 'string', 'max:255', 'unique:menus,route_name'],
            'section' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_sidebar' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Menu::create([
            'name' => $request->name,
            'route_name' => $request->route_name,
            'section' => $request->section,
            'icon' => $request->icon,
            'sort_order' => $request->input('sort_order', 0),
            'is_sidebar' => (bool) $request->boolean('is_sidebar'),
            'description' => $request->description,
        ]);

        return redirect()->route('settings.index')->with('status', 'Menu berhasil ditambahkan.');
    }

    public function updateMenu(Request $request, Menu $menu): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'route_name' => ['required', 'string', 'max:255', 'unique:menus,route_name,' . $menu->id],
            'section' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_sidebar' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $menu->update([
            'name' => $request->name,
            'route_name' => $request->route_name,
            'section' => $request->section,
            'icon' => $request->icon,
            'sort_order' => $request->input('sort_order', 0),
            'is_sidebar' => (bool) $request->boolean('is_sidebar'),
            'description' => $request->description,
        ]);

        return redirect()->route('settings.index')->with('status', 'Menu berhasil diperbarui.');
    }

    public function destroyMenu(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('settings.index')->with('status', 'Menu berhasil dihapus.');
    }

    public function updateRolePermissions(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'permissions' => ['array'],
            'permissions.*.can_view' => ['boolean'],
            'permissions.*.can_create' => ['boolean'],
            'permissions.*.can_edit' => ['boolean'],
            'permissions.*.can_delete' => ['boolean'],
            'permissions.*.can_post' => ['boolean'],
            'permissions.*.can_void' => ['boolean'],
            'permissions.*.can_export' => ['boolean'],
        ]);

        $permissions = [];

        foreach ($request->input('permissions', []) as $menuId => $permissionValues) {
            $permissions[$menuId] = [
                'can_view' => isset($permissionValues['can_view']),
                'can_create' => isset($permissionValues['can_create']),
                'can_edit' => isset($permissionValues['can_edit']),
                'can_delete' => isset($permissionValues['can_delete']),
                'can_post' => isset($permissionValues['can_post']),
                'can_void' => isset($permissionValues['can_void']),
                'can_export' => isset($permissionValues['can_export']),
            ];
        }

        $role->menus()->sync($permissions);

        return redirect()->route('settings.index')->with('status', 'Hak akses role berhasil diperbarui.');
    }
}
