@extends('layouts.app')

@section('title', 'Tambah Rekening')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="page-title mb-1">Tambah Rekening</h1>
        <p class="text-muted small mb-0">Tambahkan rekening bank baru untuk dokumen transaksi.</p>
    </div>

    <div class="card shadow-sm" style="max-width: 720px">
        <div class="card-body">
            @include('rekening._form', ['isEditing' => false, 'rekening' => null])
        </div>
    </div>
</div>
@endsection
