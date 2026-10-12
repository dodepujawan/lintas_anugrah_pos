<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserPermissionController extends Controller
{
    public function index()
    {
        $roles = Role::all();

        return view('permissions.permissions', compact('roles'));
    }

    public function getPermissions($id)
    {
        $role = Role::findOrFail($id);

        return response()->json([
            'permissions' => $role->getPermissionNames()
        ]);
    }

    public function update(Request $request)
    {
        $role = Role::findOrFail($request->role_id);

        $role->syncPermissions($request->permissions ?? []);

        return response()->json([
            'success' => true
        ]);
    }
}
