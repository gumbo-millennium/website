@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Forbidden'))

@section('image')
<div style="background-image: url({{ asset('/images/403.svg') }});"
    class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
</div>
@endsection
