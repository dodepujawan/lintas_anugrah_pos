@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form id="formExpedisiEdit" method="POST" action="{{ route('expedisi.update', $expedisi) }}">
        @csrf
        @method('PUT')
        @include('expedisi._form', ['expedisi' => $expedisi])
    </form>
</div>
@endsection
