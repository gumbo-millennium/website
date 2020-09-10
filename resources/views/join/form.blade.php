@extends('layout.variants.login')

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__title login__title-fat">Lid worden?</h1>
<p class="login__subtitle">That's awesome, maar dan moeten we nog wat gegevens hebben.</p>

{{-- Add errors --}}
@if($errors->any())
<div class="form__field-error" role="alert">
    <strong>{{ $errors->first() }}</strong>
</div>
@endif

{{-- Add intro --}}
<div class="mb-4">
    <p class="leading-relaxed mb-2">
        Hoi!<br />
        Wat leuk dat je lid wil worden van Gumbo Millennium. Vul onderstaand
        formulier in om lid te worden.
    </p>
    @include('join.partials.data-safety')
</div>

{{-- Add gender suggestions --}}
<datalist id="join-gender">
    <option value="Man">
    <option value="Vrouw">
    <option value="-">
</datalist>

{{-- Add form --}}
@include('join.partials.form')

@endsection
