@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiExpedisiCreate">
    <form id="formExpedisiCreate" data-debug="transaksiExpedisiCreateForm" method="POST" action="{{ route('expedisi.store') }}">
        @csrf
        @include('expedisi._form', ['expedisi' => null])
    </form>
</div>
@endsection
