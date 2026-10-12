<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();

            if (! Auth::check() || ! ($user instanceof User) || ! $user->hasMenuPermission('users', 'view')) {
                return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki hak akses untuk menu ini.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $users = User::with('role')
            ->orderBy('name')
            ->paginate(25);

        // gather last login from sessions table if present
        $sessions = DB::table('sessions')
            ->select('user_id', DB::raw('MAX(last_activity) as last_activity'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->pluck('last_activity', 'user_id');

        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'sessions', 'roles'));
    }

    public function create()
    {
        // handled via modal
        return redirect()->route('users.index');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'is_active' => $data['is_active'],
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        // handled via modal; redirect to index
        return redirect()->route('users.index');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();


        $user->fill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user = $user->fresh();

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (! ($currentUser instanceof User) || ! $currentUser->hasMenuPermission('users', 'delete')) {
            return redirect()->route('users.index')->with('error', 'Anda tidak memiliki hak akses untuk menu ini.');
        }

        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword(ResetPasswordRequest $request, User $user)
    {
        $currentUser = Auth::user();

        if (! ($currentUser instanceof User) || ! $currentUser->hasMenuPermission('users', 'edit')) {
            return redirect()->route('users.index')->with('error', 'Anda tidak memiliki hak akses untuk menu ini.');
        }

        $data = $request->validated();

        $user->password = Hash::make($data['password']);
        $user->save();

        return redirect()->route('users.index')->with('success', 'Password berhasil direset.');
    }
}
