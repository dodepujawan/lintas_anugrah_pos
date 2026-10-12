@extends('layouts.app')

@section('title', 'Edit Akun')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-pencil"></i> Edit Akun</h2>
        <hr>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('coa.update', $coa->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_page" value="{{ request()->query('page', 1) }}">
            <input type="hidden" name="return_q" value="{{ request()->query('q', '') }}">

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="kode_akun" class="form-label">Kode Akun <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('kode_akun') is-invalid @enderror" id="kode_akun" name="kode_akun" value="{{ old('kode_akun', $coa->kode_akun) }}" required maxlength="20">
                    @error('kode_akun')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-8">
                    <label for="nama_akun" class="form-label">Nama Akun <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_akun') is-invalid @enderror" id="nama_akun" name="nama_akun" value="{{ old('nama_akun', $coa->nama_akun) }}" required maxlength="100">
                    @error('nama_akun')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label for="tipe" class="form-label">Tipe Akun <span class="text-danger">*</span></label>
                    <select class="form-select @error('tipe') is-invalid @enderror" id="tipe" name="tipe" required>
                        <option value="">-- Pilih Tipe --</option>
                        @foreach($tipeList as $tipe)
                        <option value="{{ $tipe }}" {{ old('tipe', $coa->tipe) == $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                        @endforeach
                    </select>
                    @error('tipe')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="level" class="form-label">Level Akun <span class="text-danger">*</span></label>
                    <select class="form-select @error('level') is-invalid @enderror" id="level" name="level" required>
                        <option value="">-- Pilih Level --</option>
                        @foreach($levelList as $level)
                        <option value="{{ $level }}" {{ old('level', $coa->level) == $level ? 'selected' : '' }}>{{ $level }}</option>
                        @endforeach
                    </select>
                    @error('level')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3" id="parentGroup">
                    <label for="parent_id" class="form-label">Parent Akun</label>
                    <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                        <option value="">-- Pilih Parent --</option>
                        @foreach($parentOptions as $parentLevel => $parents)
                        @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" data-level="{{ $parentLevel }}" {{ old('parent_id', $coa->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->kode_akun }} - {{ $parent->nama_akun }} ({{ $parentLevel }})
                        </option>
                        @endforeach
                        @endforeach
                    </select>
                    <div class="form-text">Header tidak memiliki parent; Sub Header hanya memilih Header; Kelompok Akun hanya memilih Sub Header; Akun Posting hanya memilih Kelompok Akun.</div>
                    @error('parent_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="saldo_normal" class="form-label">Saldo Normal <span class="text-danger">*</span></label>
                    <select class="form-select @error('saldo_normal') is-invalid @enderror" id="saldo_normal" name="saldo_normal" required>
                        <option value="">-- Pilih Saldo Normal --</option>
                        <option value="Debit" {{ old('saldo_normal', $coa->saldo_normal) == 'Debit' ? 'selected' : '' }}>Debit</option>
                        <option value="Kredit" {{ old('saldo_normal', $coa->saldo_normal) == 'Kredit' ? 'selected' : '' }}>Kredit</option>
                    </select>
                    @error('saldo_normal')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoriList as $kategori)
                        <option value="{{ $kategori }}" {{ old('kategori', $coa->kategori) == $kategori ? 'selected' : '' }}>{{ $kategori }}</option>
                        @endforeach
                    </select>
                    @error('kategori')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $coa->deskripsi) }}</textarea>
                    @error('deskripsi')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $coa->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Akun Aktif</label>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('coa.index', ['page' => request()->query('page', 1), 'q' => request()->query('q', ''), 'focus' => $coa->id]) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const levelSelect = document.getElementById('level');
        const parentSelect = document.getElementById('parent_id');
        const parentGroup = document.getElementById('parentGroup');

        const allowedParentsMap = {
            'Header': [],
            'Sub Header': ['Header'],
            'Kelompok Akun': ['Sub Header'],
            'Akun Posting': ['Kelompok Akun'],
        };

        function updateParentOptions() {
            const selectedLevel = levelSelect.value;
            const allowedParents = allowedParentsMap[selectedLevel] || [];
            let selectedValue = parentSelect.value;
            let keepSelected = false;

            Array.from(parentSelect.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const optionLevel = option.dataset.level || '';
                const show = allowedParents.includes(optionLevel);
                option.hidden = !show;
                option.disabled = !show;

                if (show && option.value === selectedValue) {
                    keepSelected = true;
                }
            });

            if (allowedParents.length === 0) {
                parentGroup.classList.add('d-none');
                parentSelect.value = '';
            } else {
                parentGroup.classList.remove('d-none');
                if (!keepSelected) {
                    parentSelect.value = '';
                }
            }
        }

        levelSelect.addEventListener('change', updateParentOptions);
        updateParentOptions();
    });
</script>
@endpush
@endsection