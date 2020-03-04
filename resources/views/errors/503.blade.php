@extends('layout.variants.error')

@section('title', 'Systeem tijdelijk niet beschikbaar')
@section('code', '503 Service Unavailable')
@section('message')
@if ($exception->getMessage())
{{ __($exception->getMessage()) }}
@else
De site is tijdelijk niet beschikbaar.<br />
Probeer het later nog eens
@endif
@endsection

