@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form id="formExpedisiCreate" method="POST" action="{{ route('expedisi.store') }}">
        @csrf
        @include('expedisi._form', ['expedisi' => null])
    </form>
</div>
@endsection
