<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'role_menu')
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

    public function hasPermissionForMenu(Menu $menu, string $permission): bool
    {
        if (! in_array($permission, ['view', 'create', 'edit', 'delete', 'post', 'void', 'export'], true)) {
            return false;
        }

        return (bool) $this->menus()
            ->where('menus.id', $menu->id)
            ->wherePivot("can_{$permission}", true)
            ->exists();
    }
}
