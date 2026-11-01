@extends('layouts.app')

@section('title', 'Edit Rekening')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="page-title mb-1">Edit Rekening</h1>
        <p class="text-muted small mb-0">Perbarui data rekening {{ $rekening->NOREK }}.</p>
    </div>

    <div class="card shadow-sm" style="max-width: 720px">
        <div class="card-body">
            @include('rekening._form', ['isEditing' => true, 'rekening' => $rekening])
        </div>
    </div>
</div>
@endsection
