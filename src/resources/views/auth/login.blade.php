<x-guest-layout>
    <div class="auth-card">

        <!-- Logo & Header -->
        <div class="text-center mb-4">
            <div class="auth-logo mx-auto mb-3">
                📊
            </div>
            <h1 class="auth-title mb-1">{{ config('app.name') }}</h1>
            <p class="auth-subtitle">{{ config('app.tagline') }}</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
        <div class="alert-success-auth mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email / User ID -->
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="bi bi-envelope me-1"></i> Email atau Username
                </label>
                <input id="email"
                    type="text"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control auth-input @error('email') is-invalid @enderror"
                    placeholder="email atau username"
                    required
                    autofocus
                    autocomplete="username">
                @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-1"></i> Password
                </label>
                <div class="position-relative">
                    <input id="password"
                        type="password"
                        name="password"
                        class="form-control auth-input @error('password') is-invalid @enderror"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password">
                    <button type="button"
                        onclick="togglePassword()"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#64748b;cursor:pointer;padding:0;">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
                @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                    <label for="remember_me" class="form-check-label">Ingat saya</label>
                </div>
                @if (\Illuminate\Support\Facades\Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-link">
                    Lupa password?
                </a>
                @endif
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-auth">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>

        </form>

        @auth
        @if (auth()->user()?->hasMenuPermission('register', 'create') && \Illuminate\Support\Facades\Route::has('register'))
        <hr style="border-color:rgba(255,255,255,0.08);margin:1.25rem 0;">
        <div class="text-center">
            <p class="auth-subtitle mb-2" style="font-size:0.82rem;">Admin dapat membuat akun baru</p>
            <a href="{{ route('register') }}" class="btn-auth-outline">
                <i class="bi bi-person-plus me-2"></i>Daftar Akun Baru
            </a>
        </div>
        @endif
        @endauth

        <!-- Footer -->
        <div class="text-center mt-4">
            <p class="auth-subtitle" style="font-size:0.78rem;">
                &copy; {{ date('Y') }} {{ config('app.name') }} &mdash; All rights reserved
            </p>
        </div>

    </div>

    <style>
        .btn-auth-outline {
            display: block;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            color: #a5b4fc;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.65rem 1.5rem;
            width: 100%;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-auth-outline:hover {
            background: rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.4);
            color: #c7d2fe;
        }
    </style>

    <script>
        function togglePassword() {
            var input = document.getElementById('password');
            var icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    </script>
</x-guest-layout>
