@extends('layout.variants.login')

@php
$formOptions = [
    'url' => route('verification.resend'),
    'method' => 'POST'
];

$form = \FormBuilder::plain($formOptions)->add('submit', 'submit', [
    'label' => 'Verificatie opnieuw versturen'
]);
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__title">E-mailadres <strong class="login__title-fat">bevestigen</strong></h1>
<p class="login__subtitle italic">"I am not a robot", maar dan voor je e-mailadres.</p>

@if (session('resent'))
<div class="notice" role="alert">
    {{ __('A fresh verification link has been sent to your email address.') }}
</div>
@endif

<p class="mb-8">Voordat je verder gaat, moet je je e-mailadres verifiëren</p>

{{-- Form --}}
<p>Geen e-mail ontvangen, of kan je 'm even niet vinden? Klik dan hieronder om een nieuwe verificatiemail aan te vragen.</p>
{!! form($form, ['class' => 'form']) !!}
@endsection
