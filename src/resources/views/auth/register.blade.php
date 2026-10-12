<x-guest-layout>
    <div class="auth-card">

        <!-- Logo & Header -->
        <div class="text-center mb-4">
            <div class="auth-logo mx-auto mb-3">
                📊
            </div>
            <h1 class="auth-title mb-1">Buat Akun {{ config('app.name') }}</h1>
            <p class="auth-subtitle">{{ config('app.tagline') }}</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label">
                    <i class="bi bi-person me-1"></i> Nama Lengkap
                </label>
                <input id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-control auth-input @error('name') is-invalid @enderror"
                    placeholder="Nama lengkap Anda"
                    required
                    autofocus
                    autocomplete="name">
                @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Username -->
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="bi bi-person-circle me-1"></i> Username
                </label>
                <input id="username"
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    class="form-control auth-input @error('username') is-invalid @enderror"
                    placeholder="username"
                    required
                    autocomplete="username">
                @error('username')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="bi bi-envelope me-1"></i> Email
                </label>
                <input id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control auth-input @error('email') is-invalid @enderror"
                    placeholder="nama@email.com"
                    required
                    autocomplete="email">
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
                        placeholder="Min. 8 karakter"
                        required
                        autocomplete="new-password">
                    <button type="button"
                        onclick="togglePassword('password','eyeIcon1')"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#64748b;cursor:pointer;padding:0;">
                        <i class="bi bi-eye" id="eyeIcon1"></i>
                    </button>
                </div>
                @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="password_confirmation" class="form-label">
                    <i class="bi bi-lock-fill me-1"></i> Konfirmasi Password
                </label>
                <div class="position-relative">
                    <input id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-control auth-input"
                        placeholder="Ulangi password"
                        required
                        autocomplete="new-password">
                    <button type="button"
                        onclick="togglePassword('password_confirmation','eyeIcon2')"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#64748b;cursor:pointer;padding:0;">
                        <i class="bi bi-eye" id="eyeIcon2"></i>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-auth">
                <i class="bi bi-person-check me-2"></i>Daftar Sekarang
            </button>

        </form>

        <!-- Login Link -->
        <hr style="border-color:rgba(255,255,255,0.08);margin:1.25rem 0;">
        <div class="text-center">
            <p class="auth-subtitle mb-2" style="font-size:0.82rem;">Sudah punya akun?</p>
            <a href="{{ route('login') }}" class="btn-auth-outline">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Akun
            </a>
        </div>

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
        function togglePassword(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
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
