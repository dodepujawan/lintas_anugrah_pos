<form method="POST" action="{{ $isEditing ? route('rekening.update', $rekening) : route('rekening.store') }}" data-debug="extraRekeningForm">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="BANK" class="form-label">Nama Bank</label>
        <input type="text" id="BANK" name="BANK" class="form-control @error('BANK') is-invalid @enderror" maxlength="40" value="{{ old('BANK', $rekening?->BANK) }}" required autofocus>
        @error('BANK')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="NOREK" class="form-label">Nomor Rekening</label>
        <input type="text" id="NOREK" name="NOREK" class="form-control @error('NOREK') is-invalid @enderror" maxlength="40" value="{{ old('NOREK', $rekening?->NOREK) }}" required>
        @error('NOREK')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="NAMA" class="form-label">Nama Pemilik <span class="text-muted">(opsional)</span></label>
        <input type="text" id="NAMA" name="NAMA" class="form-control @error('NAMA') is-invalid @enderror" maxlength="40" value="{{ old('NAMA', $rekening?->NAMA) }}">
        @error('NAMA')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if (! $isEditing)
        <div class="form-text mb-3">Rekening baru tidak langsung aktif. Pilih rekening aktif dari daftar setelah disimpan.</div>
    @endif

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" data-debug="extraRekeningSaveBtn">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Rekening' }}</button>
        <a href="{{ route('rekening.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
