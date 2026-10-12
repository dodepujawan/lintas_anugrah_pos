<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index(){
        return view('dashboard.container');
    }

    public function login()
    {
        if (Auth::check()) {
            return redirect('login/home');
        }else{
            return view('login.login');
        }
    }

    public function actionlogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Simpan session
            $user = Auth::user();
            session([
                'id' => $user->id,
                'name' => $user->name,
                'role_old' => $user->roles,
                'user_id' => $user->user_id,
            ]);

            return redirect()->intended('login/home');
        } else {
            // Tambahkan pesan error
            return redirect()->back()->with('error', 'Email atau Password Salah');
        }
    }

    public function actionlogout()
    {
        session()->forget(['id', 'name', 'role_old', 'user_id']);

        Auth::logout();
        return redirect('/');
    }
}
