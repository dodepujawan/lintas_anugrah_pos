@extends('layouts.app')

@section('title', 'Profil & Password')

@section('content')
<div class="container-fluid py-3 px-2 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h1 class="h4 mb-1"><i class="bi bi-person-gear me-2"></i>Profil & Password</h1>
                    <div class="text-muted small">Kelola email dan password akun {{ auth()->user()->name }}.</div>
                </div>
                <a href="{{ url()->previous() === url()->current() ? route('dashboard') : url()->previous() }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
            </div>

            @if(session('status') === 'password-updated')
            <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i>Password berhasil diubah.</div>
            @endif

            @if(session('status') === 'email-updated')
            <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i>Email profil berhasil disimpan.</div>
            @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3 p-md-4">
                    <h2 class="h6 mb-1"><i class="bi bi-envelope me-1"></i>Email Profil</h2>
                    <p class="text-muted small mb-3">Email disimpan sebagai informasi kontak akun.</p>
                    <form method="POST" action="{{ route('profile.email.update') }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="profile_email" class="form-label fw-semibold">Alamat Email</label>
                            <input id="profile_email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" class="form-control @error('email', 'updateEmail') is-invalid @enderror" placeholder="nama@email.com" autocomplete="email" required>
                            @error('email', 'updateEmail')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-save me-1"></i>Simpan Email</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <h2 class="h6 mb-1"><i class="bi bi-key me-1"></i>Ubah Password</h2>
                    <p class="text-muted small mb-3">Masukkan password saat ini sebelum membuat password baru.</p>
                    <form method="POST" action="{{ route('password.update') }}" id="selfPasswordForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">Password Saat Ini</label>
                            <div class="input-group">
                                <input id="current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password" required autofocus>
                                <button class="btn btn-outline-secondary password-visibility" type="button" data-target="current_password" aria-label="Lihat password"><i class="bi bi-eye"></i></button>
                            </div>
                            @error('current_password', 'updatePassword')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold">Password Baru</label>
                            <div class="input-group">
                                <input id="new_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password" minlength="8" required>
                                <button class="btn btn-outline-secondary password-visibility" type="button" data-target="new_password" aria-label="Lihat password"><i class="bi bi-eye"></i></button>
                            </div>
                            <div class="form-text">Gunakan minimal 8 karakter dan jangan memakai password yang mudah ditebak.</div>
                            @error('password', 'updatePassword')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" minlength="8" required>
                                <button class="btn btn-outline-secondary password-visibility" type="button" data-target="password_confirmation" aria-label="Lihat password"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-shield-lock me-1"></i>Simpan Password Baru</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.password-visibility').forEach(function(button) {
    button.addEventListener('click', function() {
        const input = document.getElementById(button.dataset.target);
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.innerHTML = visible ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });
});
</script>
@endpush
