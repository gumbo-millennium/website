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
        Wat leuk dat je lid wil worden van Gumbo Millennium. Om je inschrijving te verwerken
        hebben we wat gegevens van je nodig. Alle gegevens worden behandeld volgens het
        <a href="{{ url('/privacy-policy') }}">privacybeleid van Gumbo Millennium</a>.
    </p>
    <p class="leading-relaxed text-sm text-gray-600">
        Omdat jouw inschrijving niet betekend dat je lid wordt, slaan wij deze gegevens versleuteld op.
        Indien na 90 dagen nog steeds geen positief besluit is genomen over je aanmelding, verwijderen wij je
        gegevens automatisch. Hiervan krijg je bericht.
    </p>
</div>

{{-- Start form --}}
{!! form_start($form, ['class' => 'form']) !!}

{{-- Part 1: Name --}}
<div class="mb-8">
    <h3 class="text-xl font-normal">Wat is je naam?</h3>
    <p>Omdat dit een lidmaatschap betreft, willen wij graag je officiële naam weten, zoals in je paspoort staat.</p>
</div>
{!! form_until($form, 'last-name') !!}

{{-- Part 2: Contact details --}}
<div class="mb-4 mt-8">
    <h3 class="text-xl font-normal">Hoe kunnen wij je bereiken?</h3>
    <p>We hebben graag een e-mailadres en telefoonnummer van onze leden, vul deze hieronder in.</p>
</div>
{!! form_until($form, 'phone') !!}

{{-- Part 3: Personal info --}}
<div class="mb-4 mt-8">
    <h3 class="text-xl font-normal">Persoonsgegevens</h3>
    <p>Dan hebben we nog wat persoonsgegevens en je adres nodig, voor onze ledenadministratie.</p>
</div>
{!! form_until($form, 'country') !!}

{{-- Part 3: Address --}}
<div class="mb-4 mt-8">
    <h3 class="text-xl font-normal">Almost there...</h3>
    <p>Je bent er bíjna, nog een paar korte vraagjes...</p>
</div>
{!! form_rest($form) !!}

{{-- Done :) --}}
{!! form_end($form) !!}
@endsection
