@extends('layout.variants.login')

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__title">Do you wanna <strong class="login__title-fat">become a member</strong>?</h1>
<p class="login__subtitle">Awesome dat je lid wil worden, tijd voor wat gegevens.</p>

<div class="mb-4">
    <p>
        Hoi!<br />
        Wat leuk dat je lid wil worden van Gumbo Millennium. Om je inschrijving te verwerken
        hebben we wat gegevens van je nodig. Alle gegevens worden behandeld volgens het
        <a href="{{ url('/privacy-policy') }}">privacybeleid van Gumbo Millennium</a>.
    </p>
</div>

{{-- Error --}}
@if($errors->any())
<div class="form__field-error" role="alert">
    <strong>{{ $errors->first() }}</strong>
</div>
@endif

{{-- Render form --}}
{!! form($form, ['class' => 'form']) !!}
@endsection
