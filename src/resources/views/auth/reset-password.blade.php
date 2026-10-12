<x-guest-layout>
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="auth-logo mx-auto mb-3"><i class="bi bi-shield-lock"></i></div>
            <h1 class="auth-title mb-1">Buat Password Baru</h1>
            <p class="auth-subtitle">Gunakan minimal 8 karakter yang tidak mudah ditebak.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" class="form-control auth-input @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" readonly>
                @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password Baru</label>
                <input id="password" class="form-control auth-input @error('password') is-invalid @enderror" type="password" name="password" minlength="8" required autocomplete="new-password">
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                <input id="password_confirmation" class="form-control auth-input" type="password" name="password_confirmation" minlength="8" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn-auth"><i class="bi bi-check-circle me-2"></i>Simpan Password Baru</button>
        </form>
    </div>
</x-guest-layout>
