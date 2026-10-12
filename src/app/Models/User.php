<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;
use App\Models\Menu;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'role_old',
        'is_admin',
        'is_active',
        'user_id',
        'area_id',
        'area_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role_id' => 'integer',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
        'user_id' => 'integer',
        'area_id' => 'integer',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasMenuPermission(string $routeName, string $permission = 'view'): bool
    {
        $menu = Menu::resolveByRoute($routeName);

        if (! $menu) {
            return false;
        }

        if (! in_array($permission, ['view', 'create', 'edit', 'delete', 'post', 'void', 'export'], true)) {
            return false;
        }

        return $this->hasPermissionForMenu($menu, $permission);
    }

    public function hasPermissionForMenu(Menu $menu, string $permission = 'view'): bool
    {
        if (! in_array($permission, ['view', 'create', 'edit', 'delete', 'post', 'void', 'export'], true)) {
            return false;
        }

        $this->loadMissing('role.menus');

        if ($this->role === null) {
            return false;
        }

        if ($this->role->name === 'Administrator' && $menu->route_name === 'settings') {
            return true;
        }

        $roleMenu = $this->role->menus->firstWhere('id', $menu->id);

        return (bool) ($roleMenu?->pivot->{"can_{$permission}"} ?? false);
    }

    public function hasExplicitMenuPermission(string $routeName, string $permission = 'view'): bool
    {
        $menu = Menu::resolveByRoute($routeName);

        if (! $menu || ! in_array($permission, ['view', 'create', 'edit', 'delete', 'post', 'void', 'export'], true)) {
            return false;
        }

        $this->loadMissing('role.menus');

        if ($this->role === null) {
            return false;
        }

        $roleMenu = $this->role->menus->firstWhere('id', $menu->id);

        return (bool) ($roleMenu?->pivot->{"can_{$permission}"} ?? false);
    }

    /**
     * Route awal yang aman bagi user sesuai hak menu yang dimilikinya.
     */
    public function defaultAuthorizedRoute(): string
    {
        foreach (Menu::sidebarForUser($this) as $menu) {
            if (Route::has($menu->route_name)) {
                return $menu->route_name;
            }

            if (Route::has($menu->route_name.'.index')) {
                return $menu->route_name.'.index';
            }
        }

        // Tetap sediakan halaman aman ketika role belum memiliki satu pun menu.
        return 'profile.edit';
    }

    public function getAuthIdentifierName(): string
    {
        return $this->getKeyName();
    }

    public function findForAuth(string $login): ?self
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return $this->where('email', $login)->first();
        }

        if (User::where('username', $login)->exists()) {
            return $this->where('username', $login)->first();
        }

        if (ctype_digit($login)) {
            return $this->where('id', $login)->first();
        }

        return $this->where('username', $login)->first();
    }
}
