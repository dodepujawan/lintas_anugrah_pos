<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View|RedirectResponse
    {
        $currentUser = Auth::user();

        if (! Auth::check() || ! ($currentUser instanceof User) || ! $currentUser->hasMenuPermission('register', 'create')) {
            return redirect(route('dashboard', absolute: false));
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();

        if (! Auth::check() || ! ($currentUser instanceof User) || ! $currentUser->hasMenuPermission('register', 'create')) {
            throw ValidationException::withMessages([
                'email' => 'Hanya pengguna dengan izin yang dapat membuat akun baru.',
            ]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:' . User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        return redirect(route('dashboard', absolute: false))->with('status', 'Akun berhasil dibuat.');
    }
}
